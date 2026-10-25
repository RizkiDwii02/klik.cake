<?php

namespace Tests\Unit;

use App\Exceptions\SlotFullException;
use App\Models\SlotQuota;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Tests\TestCase;

class SlotAvailabilityServiceTest extends TestCase
{
    protected SlotAvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SlotAvailabilityService(cutoffHour: 18, defaultCapacity: 5);
    }

    public function test_standard_lead_time_allows_tomorrow_before_1800(): void
    {
        // 10:00 AM on 2026-10-23 -> Tomorrow 2026-10-24 should be earliest available
        $currentTime = Carbon::create(2026, 10, 23, 10, 0, 0, 'Asia/Makassar');
        $earliest = $this->service->getEarliestAvailableDate(leadTimeDays: 1, now: $currentTime);

        $this->assertEquals('2026-10-24', $earliest->toDateString());
        $this->assertFalse($this->service->isDateAllowed('2026-10-23', leadTimeDays: 1, now: $currentTime));
        $this->assertTrue($this->service->isDateAllowed('2026-10-24', leadTimeDays: 1, now: $currentTime));
    }

    public function test_cutoff_at_1800_pushes_earliest_date_to_day_after_tomorrow(): void
    {
        // Exactly 18:00 on 2026-10-23 -> Cutoff passed, earliest must be 2026-10-25 (H+2)
        $atCutoff = Carbon::create(2026, 10, 23, 18, 0, 0, 'Asia/Makassar');
        $earliest = $this->service->getEarliestAvailableDate(leadTimeDays: 1, now: $atCutoff);

        $this->assertEquals('2026-10-25', $earliest->toDateString());
        $this->assertFalse($this->service->isDateAllowed('2026-10-24', leadTimeDays: 1, now: $atCutoff));
        $this->assertTrue($this->service->isDateAllowed('2026-10-25', leadTimeDays: 1, now: $atCutoff));

        // 17:59 -> Cutoff not reached, tomorrow (2026-10-24) is still valid
        $justBefore = Carbon::create(2026, 10, 23, 17, 59, 59, 'Asia/Makassar');
        $earliestBefore = $this->service->getEarliestAvailableDate(leadTimeDays: 1, now: $justBefore);
        $this->assertEquals('2026-10-24', $earliestBefore->toDateString());
        $this->assertTrue($this->service->isDateAllowed('2026-10-24', leadTimeDays: 1, now: $justBefore));
    }

    public function test_custom_cake_requires_h_minus_3_lead_time(): void
    {
        // 10:00 AM on 2026-10-23 with lead time 3 -> Earliest must be 2026-10-26 (H+3)
        $currentTime = Carbon::create(2026, 10, 23, 10, 0, 0, 'Asia/Makassar');
        $earliest = $this->service->getEarliestAvailableDate(leadTimeDays: 3, now: $currentTime);

        $this->assertEquals('2026-10-26', $earliest->toDateString());
        $this->assertFalse($this->service->isDateAllowed('2026-10-24', leadTimeDays: 3, now: $currentTime));
        $this->assertFalse($this->service->isDateAllowed('2026-10-25', leadTimeDays: 3, now: $currentTime));
        $this->assertTrue($this->service->isDateAllowed('2026-10-26', leadTimeDays: 3, now: $currentTime));

        // After 18:00, custom cake lead time H-3 becomes H+4 (2026-10-27)
        $afterCutoff = Carbon::create(2026, 10, 23, 19, 0, 0, 'Asia/Makassar');
        $earliestAfter = $this->service->getEarliestAvailableDate(leadTimeDays: 3, now: $afterCutoff);
        $this->assertEquals('2026-10-27', $earliestAfter->toDateString());
    }

    public function test_invalid_time_slot_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->reserveSlot('2026-10-25', '08:00');
    }
}
