<?php

namespace App\Services;

use App\Exceptions\SlotFullException;
use App\Models\SlotQuota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SlotAvailabilityService
{
    /**
     * Standard time slots available each day.
     *
     * @var array<string, string>
     */
    public const TIME_SLOTS = [
        '10:00' => '10.00 – 12.00',
        '13:00' => '13.00 – 15.00',
        '16:00' => '16.00 – 18.00',
    ];

    public function __construct(
        protected int $cutoffHour = 18,
        protected int $defaultCapacity = 5
    ) {
        if (function_exists('config') && app()->bound('config')) {
            $this->cutoffHour = (int) config('shop.schedule.cutoff_hour', $this->cutoffHour);
            $this->defaultCapacity = (int) config('shop.schedule.default_slot_capacity', $this->defaultCapacity);
        }
    }

    /**
     * Calculate the earliest date an order can be fulfilled based on lead time and 18:00 cutoff.
     */
    public function getEarliestAvailableDate(int $leadTimeDays = 1, ?Carbon $now = null): Carbon
    {
        $current = $now ? $now->copy() : Carbon::now('Asia/Makassar');
        $earliest = $current->copy()->startOfDay()->addDays($leadTimeDays);

        // If current hour has reached or passed cutoff hour (18:00), push date by +1 day
        if ($current->hour >= $this->cutoffHour) {
            $earliest->addDay();
        }

        return $earliest;
    }

    /**
     * Check if a specific target date is allowed given lead time and cutoff.
     */
    public function isDateAllowed(Carbon|string $targetDate, int $leadTimeDays = 1, ?Carbon $now = null): bool
    {
        $target = is_string($targetDate) ? Carbon::parse($targetDate)->startOfDay() : $targetDate->copy()->startOfDay();
        $earliest = $this->getEarliestAvailableDate($leadTimeDays, $now);

        return $target->greaterThanOrEqualTo($earliest);
    }

    /**
     * Get availability status of all slots for a given date.
     *
     * @return array<string, array{time_slot: string, label: string, is_available: bool, capacity: int, booked: int, remaining: int, reason: ?string}>
     */
    public function getAvailableSlots(Carbon|string $targetDate, int $leadTimeDays = 1, int $quantity = 1, ?Carbon $now = null): array
    {
        $date = is_string($targetDate) ? Carbon::parse($targetDate)->startOfDay() : $targetDate->copy()->startOfDay();
        $dateString = $date->toDateString();
        $isAllowed = $this->isDateAllowed($date, $leadTimeDays, $now);

        $quotas = SlotQuota::where('date', $dateString)->get()->keyBy('time_slot');

        $result = [];
        foreach (self::TIME_SLOTS as $slotKey => $slotLabel) {
            $quota = $quotas->get($slotKey);
            $capacity = $quota ? $quota->capacity : $this->defaultCapacity;
            $booked = $quota ? $quota->booked : 0;
            $remaining = max(0, $capacity - $booked);

            $available = $isAllowed && ($booked + $quantity <= $capacity);
            $reason = null;

            if (! $isAllowed) {
                $reason = 'Melewati batas waktu pemesanan (lead time / cut-off pukul 18.00)';
            } elseif ($booked + $quantity > $capacity) {
                $reason = 'Kuota slot penuh';
            }

            $result[$slotKey] = [
                'time_slot' => $slotKey,
                'label' => $slotLabel,
                'is_available' => $available,
                'capacity' => $capacity,
                'booked' => $booked,
                'remaining' => $remaining,
                'reason' => $reason,
            ];
        }

        return $result;
    }

    /**
     * Atomically reserve slot quota inside a DB transaction using conditional UPDATE.
     *
     * @throws SlotFullException
     */
    public function reserveSlot(Carbon|string $targetDate, string $timeSlot, int $quantity = 1, int $leadTimeDays = 1, ?Carbon $now = null): SlotQuota
    {
        $date = is_string($targetDate) ? Carbon::parse($targetDate)->startOfDay() : $targetDate->copy()->startOfDay();
        $dateString = $date->toDateString();

        if (! array_key_exists($timeSlot, self::TIME_SLOTS)) {
            throw new \InvalidArgumentException("Slot waktu {$timeSlot} tidak valid.");
        }

        if (! $this->isDateAllowed($date, $leadTimeDays, $now)) {
            throw new SlotFullException("Tanggal {$dateString} tidak memenuhi batas lead time atau telah melewati cut-off pukul 18.00.");
        }

        return DB::transaction(function () use ($dateString, $timeSlot, $quantity) {
            // Ensure slot record exists
            $slot = SlotQuota::firstOrCreate(
                ['date' => $dateString, 'time_slot' => $timeSlot],
                ['capacity' => $this->defaultCapacity, 'booked' => 0]
            );

            // Atomic conditional UPDATE: only increments if capacity is not exceeded
            $affected = DB::table('slot_quotas')
                ->where('id', $slot->id)
                ->whereRaw('(booked + ?) <= capacity', [$quantity])
                ->increment('booked', $quantity);

            if ($affected === 0) {
                throw new SlotFullException("Slot waktu {$timeSlot} pada tanggal {$dateString} sudah penuh.");
            }

            return $slot->fresh();
        });
    }

    /**
     * Atomically release / decrement booked slot quota.
     */
    public function releaseSlot(Carbon|string $targetDate, string $timeSlot, int $quantity = 1): void
    {
        $date = is_string($targetDate) ? Carbon::parse($targetDate)->startOfDay() : $targetDate->copy()->startOfDay();
        $dateString = $date->toDateString();

        DB::transaction(function () use ($dateString, $timeSlot, $quantity) {
            $slot = SlotQuota::where('date', $dateString)->where('time_slot', $timeSlot)->first();

            if ($slot) {
                DB::table('slot_quotas')
                    ->where('id', $slot->id)
                    ->update([
                        'booked' => DB::raw("GREATEST(0, CAST(booked AS SIGNED) - {$quantity})"),
                    ]);
            }
        });
    }

    /**
     * Suggest the nearest available slot starting from the preferred date and slot.
     *
     * @return array{date: string, time_slot: string, label: string, remaining: int}|null
     */
    public function suggestNearestAvailableSlot(
        Carbon|string $targetDate,
        string $preferredSlot,
        int $leadTimeDays = 1,
        int $quantity = 1,
        ?Carbon $now = null
    ): ?array {
        $earliest = $this->getEarliestAvailableDate($leadTimeDays, $now);
        $date = is_string($targetDate) ? Carbon::parse($targetDate)->startOfDay() : $targetDate->copy()->startOfDay();

        if ($date->lessThan($earliest)) {
            $date = $earliest->copy();
        }

        // Search up to 14 days ahead
        for ($i = 0; $i < 14; $i++) {
            $currentDate = $date->copy()->addDays($i);
            $availableSlots = $this->getAvailableSlots($currentDate, $leadTimeDays, $quantity, $now);

            // First check if preferred slot is available
            if ($availableSlots[$preferredSlot]['is_available'] ?? false) {
                return [
                    'date' => $currentDate->toDateString(),
                    'time_slot' => $preferredSlot,
                    'label' => self::TIME_SLOTS[$preferredSlot],
                    'remaining' => $availableSlots[$preferredSlot]['remaining'],
                ];
            }

            // Check other slots on the same day
            foreach ($availableSlots as $slotKey => $slotData) {
                if ($slotData['is_available']) {
                    return [
                        'date' => $currentDate->toDateString(),
                        'time_slot' => $slotKey,
                        'label' => $slotData['label'],
                        'remaining' => $slotData['remaining'],
                    ];
                }
            }
        }

        return null;
    }
}
