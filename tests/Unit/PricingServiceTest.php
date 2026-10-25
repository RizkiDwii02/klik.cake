<?php

namespace Tests\Unit;

use App\Enums\PromoCodeType;
use App\Models\DeliveryZone;
use App\Models\PromoCode;
use App\Services\PricingService;
use PHPUnit\Framework\TestCase;

class PricingServiceTest extends TestCase
{
    protected PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingService();
    }

    public function test_calculate_subtotal_multiplies_price_and_quantity(): void
    {
        $items = [
            ['price' => 185000, 'quantity' => 1], // Strawberry Cream Cake 16cm
            ['price' => 22000, 'quantity' => 3],  // 3 Butter Croissants (66.000)
        ];

        $subtotal = $this->service->calculateSubtotal($items);
        $this->assertEquals(251000, $subtotal);
    }

    public function test_pickup_orders_have_zero_shipping_fee(): void
    {
        $zone = new DeliveryZone(['name' => 'Makassar Kota', 'fee' => 15000, 'is_active' => true]);
        $shippingFee = $this->service->calculateShippingFee('pickup', $zone);

        $this->assertEquals(0, $shippingFee);
    }

    public function test_delivery_fee_applied_for_active_zone(): void
    {
        $zone = new DeliveryZone(['name' => 'Tamalanrea', 'fee' => 25000, 'is_active' => true]);
        $shippingFee = $this->service->calculateShippingFee('delivery', $zone);

        $this->assertEquals(25000, $shippingFee);
    }

    public function test_percentage_promo_code_applies_correct_discount_up_to_max(): void
    {
        // 10% discount, max 25.000, min order 100.000
        $promo = new PromoCode([
            'code' => 'KLIKMANIS',
            'type' => PromoCodeType::PERCENTAGE->value,
            'value' => 10,
            'min_order_amount' => 100000,
            'max_discount' => 25000,
            'is_active' => true,
        ]);

        // Case 1: Subtotal 150.000 -> 10% is 15.000 (under max 25.000)
        $discount1 = $this->service->calculateDiscount($promo, 150000);
        $this->assertEquals(15000, $discount1);

        // Case 2: Subtotal 300.000 -> 10% is 30.000, capped at max 25.000
        $discount2 = $this->service->calculateDiscount($promo, 300000);
        $this->assertEquals(25000, $discount2);

        // Case 3: Subtotal 80.000 -> below min order 100.000 -> 0 discount
        $discount3 = $this->service->calculateDiscount($promo, 80000);
        $this->assertEquals(0, $discount3);
    }

    public function test_fixed_promo_code_applies_exact_amount(): void
    {
        // Fixed 20.000 discount, min order 150.000
        $promo = new PromoCode([
            'code' => 'FRESHBAKE',
            'type' => PromoCodeType::FIXED->value,
            'value' => 20000,
            'min_order_amount' => 150000,
            'is_active' => true,
        ]);

        $discount = $this->service->calculateDiscount($promo, 185000);
        $this->assertEquals(20000, $discount);
    }

    public function test_calculate_order_summary_computes_correct_total(): void
    {
        $items = [
            ['price' => 185000, 'quantity' => 1],
        ];
        $zone = new DeliveryZone(['name' => 'Makassar Kota', 'fee' => 15000, 'is_active' => true]);
        $promo = new PromoCode([
            'code' => 'FRESHBAKE',
            'type' => PromoCodeType::FIXED->value,
            'value' => 20000,
            'min_order_amount' => 150000,
            'is_active' => true,
        ]);

        // Subtotal (185.000) + Shipping (15.000) - Promo (20.000) = 180.000
        $summary = $this->service->calculateOrderSummary($items, 'delivery', $zone, $promo);

        $this->assertEquals(185000, $summary['subtotal']);
        $this->assertEquals(15000, $summary['shipping_fee']);
        $this->assertEquals(20000, $summary['discount_amount']);
        $this->assertEquals(180000, $summary['total']);
        $this->assertEquals('Rp180.000', $summary['formatted_total']);
    }
}
