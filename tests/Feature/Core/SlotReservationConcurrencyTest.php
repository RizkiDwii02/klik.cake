<?php

namespace Tests\Feature\Core;

use App\Exceptions\SlotFullException;
use App\Models\SlotQuota;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SlotReservationConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected SlotAvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Strict safety check: Ensure tests ONLY run on test database, NEVER on development db
        $this->assertEquals(
            'klikcake_test',
            DB::connection()->getDatabaseName(),
            'CRITICAL SAFETY ERROR: Tests must only run on the dedicated test database [klikcake_test]!'
        );

        $this->service = new SlotAvailabilityService(cutoffHour: 18, defaultCapacity: 5);
    }

    public function test_atomic_slot_reservation_succeeds_when_capacity_available(): void
    {
        $testDate = Carbon::now('Asia/Makassar')->startOfDay()->addDays(2)->toDateString();
        $timeSlot = '10:00';

        // 1. Initial reservation of 3 slots
        $slot = $this->service->reserveSlot($testDate, $timeSlot, quantity: 3);

        $this->assertEquals(3, $slot->booked);
        $this->assertEquals(5, $slot->capacity);
        $this->assertEquals(2, $slot->remainingCapacity());

        // 2. Second reservation of remaining 2 slots
        $slot2 = $this->service->reserveSlot($testDate, $timeSlot, quantity: 2);
        $this->assertEquals(5, $slot2->booked);
        $this->assertEquals(0, $slot2->remainingCapacity());
    }

    public function test_slot_full_is_rejected_and_booked_remains_unchanged(): void
    {
        $testDate = Carbon::now('Asia/Makassar')->startOfDay()->addDays(2)->toDateString();
        $timeSlot = '13:00';

        // Fill slot to 4 out of 5
        $this->service->reserveSlot($testDate, $timeSlot, quantity: 4);

        $slot = SlotQuota::where('date', $testDate)->where('time_slot', $timeSlot)->first();
        $this->assertEquals(4, $slot->booked);

        // Trying to book 2 more (4 + 2 = 6 > capacity 5) MUST throw SlotFullException
        $this->expectException(SlotFullException::class);

        try {
            $this->service->reserveSlot($testDate, $timeSlot, quantity: 2);
        } finally {
            // Confirm atomic integrity: booked must STILL be 4
            $refreshed = SlotQuota::where('date', $testDate)->where('time_slot', $timeSlot)->first();
            $this->assertEquals(4, $refreshed->booked);
        }
    }

    public function test_suggest_nearest_available_slot_when_preferred_is_full(): void
    {
        $testDate = Carbon::now('Asia/Makassar')->startOfDay()->addDays(2);
        $dateString = $testDate->toDateString();

        // Fill slot 10:00 completely (5 out of 5)
        $this->service->reserveSlot($dateString, '10:00', quantity: 5);

        // Preferred was 10:00, but it is full -> suggest nearest available on the same day (13:00)
        $suggested = $this->service->suggestNearestAvailableSlot($testDate, preferredSlot: '10:00', quantity: 1);

        $this->assertNotNull($suggested);
        $this->assertEquals($dateString, $suggested['date']);
        $this->assertEquals('13:00', $suggested['time_slot']);
        $this->assertEquals(5, $suggested['remaining']);
    }

    public function test_release_slot_decrements_booked_count(): void
    {
        $testDate = Carbon::now('Asia/Makassar')->startOfDay()->addDays(2)->toDateString();
        $timeSlot = '16:00';

        // Reserve 3
        $this->service->reserveSlot($testDate, $timeSlot, quantity: 3);
        $this->assertEquals(3, SlotQuota::where('date', $testDate)->where('time_slot', $timeSlot)->first()->booked);

        // Release 1
        $this->service->releaseSlot($testDate, $timeSlot, quantity: 1);
        $this->assertEquals(2, SlotQuota::where('date', $testDate)->where('time_slot', $timeSlot)->first()->booked);
    }
}
