<?php

namespace Tests\Unit;

use App\Services\OrderNumberGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;
    public function test_order_number_format_matches_pattern(): void
    {
        $generator = new OrderNumberGenerator();
        $date = Carbon::create(2026, 10, 23);
        $orderNumber = $generator->generate($date);

        // Pattern: KC-YYYYMMDD-XXXX (e.g. KC-20261023-0001)
        $this->assertMatchesRegularExpression('/^KC-20261023-\d{4}$/', $orderNumber);
    }
}
