<?php

namespace App\Filament\Pages;

use App\Models\SlotQuota;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class FreshBakeCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static \UnitEnum|string|null $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Kalender Fresh Bake';

    protected static ?string $title = 'Kalender Kuota Fresh Bake';

    protected string $view = 'filament.pages.fresh-bake-calendar';

    /**
     * @return array<int, array{date: string, formatted_date: string, day_name: string, is_today: bool, slots: array<string, array{capacity: int, booked: int, remaining: int, is_full: bool}>}>
     */
    public function getDaysDataProperty(): array
    {
        $startDate = Carbon::now('Asia/Makassar')->startOfDay();
        $days = [];

        $timeSlots = ['10:00', '13:00', '16:00'];
        $quotas = SlotQuota::where('date', '>=', $startDate->format('Y-m-d'))
            ->where('date', '<=', $startDate->copy()->addDays(13)->format('Y-m-d'))
            ->get()
            ->groupBy(fn ($q) => Carbon::parse($q->date)->format('Y-m-d'));

        for ($i = 0; $i < 14; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dateStr = $currentDate->format('Y-m-d');
            $dateQuotas = $quotas->get($dateStr, collect());

            $slotData = [];
            foreach ($timeSlots as $slot) {
                $quota = $dateQuotas->firstWhere('time_slot', $slot);
                $capacity = $quota ? (int) $quota->capacity : 5;
                $booked = $quota ? (int) $quota->booked : 0;
                $remaining = max(0, $capacity - $booked);
                $isFull = $booked >= $capacity;

                $slotData[$slot] = [
                    'id' => $quota?->id,
                    'capacity' => $capacity,
                    'booked' => $booked,
                    'remaining' => $remaining,
                    'is_full' => $isFull,
                ];
            }

            $days[] = [
                'date' => $dateStr,
                'formatted_date' => $currentDate->locale('id')->translatedFormat('d M Y'),
                'day_name' => $currentDate->locale('id')->translatedFormat('l'),
                'is_today' => $i === 0,
                'slots' => $slotData,
            ];
        }

        return $days;
    }

    /**
     * Update slot capacity directly from calendar grid.
     */
    public function updateSlotCapacity(string $date, string $timeSlot, int $newCapacity): void
    {
        $newCapacity = max(0, $newCapacity);

        $quota = SlotQuota::firstOrCreate(
            ['date' => $date, 'time_slot' => $timeSlot],
            ['capacity' => 5, 'booked' => 0]
        );

        $quota->capacity = $newCapacity;
        $quota->save();

        Notification::make()
            ->title('Kapasitas Slot Diperbarui')
            ->body("Kapasitas slot {$timeSlot} pada tanggal " . Carbon::parse($date)->format('d/m/Y') . " kini diubah menjadi {$newCapacity}.")
            ->success()
            ->send();
    }
}
