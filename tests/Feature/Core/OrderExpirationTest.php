<?php

namespace Tests\Feature\Core;

use App\Enums\InternalOrderStatus;
use App\Models\Collection;
use App\Models\Order;
use App\Models\Product;
use App\Models\SlotQuota;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_expire_orders_command_cancels_expired_orders_and_releases_resources(): void
    {
        $date = Carbon::now('Asia/Makassar')->addDays(2)->format('Y-m-d');
        $slotQuota = SlotQuota::create([
            'date' => $date,
            'time_slot' => '10:00',
            'capacity' => 5,
            'booked' => 3,
        ]);

        $collection = Collection::create([
            'name' => 'Pastry',
            'slug' => 'pastry-' . uniqid(),
        ]);

        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Choco Danish',
            'slug' => 'cd-' . uniqid(),
            'portion_info' => '1 orang',
            'lead_time_days' => 1,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => 'Original',
            'sku' => 'CD-ORIG-' . uniqid(),
            'price' => 35000,
            'stock' => 5,
        ]);

        // 1. Expired Order (created earlier, expires_at was 30 minutes ago)
        $expiredOrder = Order::create([
            'order_number' => 'KC-EXP-001',
            'customer_name' => 'Alwi Dahlan',
            'customer_phone' => '085211223344',
            'delivery_type' => 'pickup',
            'delivery_address' => 'Ambil di Toko',
            'subtotal' => 70000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 70000,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => 'Diterima',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
            'expires_at' => Carbon::now('Asia/Makassar')->subMinutes(30),
        ]);

        $expiredOrder->items()->create([
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'price' => 35000,
            'quantity' => 2,
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
        ]);

        // 2. Active, unexpired Order (expires in 45 minutes)
        $activeOrder = Order::create([
            'order_number' => 'KC-ACT-002',
            'customer_name' => 'Fadli Rahman',
            'customer_phone' => '085299887766',
            'delivery_type' => 'pickup',
            'delivery_address' => 'Ambil di Toko',
            'subtotal' => 35000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 35000,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => 'Diterima',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
            'expires_at' => Carbon::now('Asia/Makassar')->addMinutes(45),
        ]);

        $activeOrder->items()->create([
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'price' => 35000,
            'quantity' => 1,
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
        ]);

        // Run artisan orders:expire command
        $this->artisan('orders:expire')
            ->expectsOutputToContain('Menemukan 1 pesanan kedaluwarsa')
            ->assertSuccessful();

        $expiredOrder->refresh();
        $this->assertEquals(InternalOrderStatus::CANCELLED->value, $expiredOrder->internal_status);

        $activeOrder->refresh();
        $this->assertEquals(InternalOrderStatus::ORDER_PLACED->value, $activeOrder->internal_status);

        // Variant stock should have restored 2 items (from 5 to 7)
        $variant->refresh();
        $this->assertEquals(7, $variant->stock);

        // Slot quota booked count should decrease by 2 (from 3 to 1)
        $slotQuota->refresh();
        $this->assertEquals(1, $slotQuota->booked);
    }

    public function test_lazy_check_on_access_expires_order_immediately(): void
    {
        $date = Carbon::now('Asia/Makassar')->addDays(2)->format('Y-m-d');
        $slotQuota = SlotQuota::create([
            'date' => $date,
            'time_slot' => '13:00',
            'capacity' => 5,
            'booked' => 1,
        ]);

        $collection = Collection::create([
            'name' => 'Pastry',
            'slug' => 'pastry-' . uniqid(),
        ]);

        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Cheese Danish',
            'slug' => 'chd-' . uniqid(),
            'portion_info' => '1 orang',
            'lead_time_days' => 1,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => 'Keju',
            'sku' => 'CHD-KJ-' . uniqid(),
            'price' => 40000,
            'stock' => 5,
        ]);

        $order = Order::create([
            'order_number' => 'KC-LAZY-003',
            'customer_name' => 'Rina Wijaya',
            'customer_phone' => '081299887711',
            'delivery_type' => 'pickup',
            'delivery_address' => 'Ambil di Toko',
            'subtotal' => 40000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 40000,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => 'Diterima',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '13:00',
            'expires_at' => Carbon::now('Asia/Makassar')->subMinute(),
        ]);

        $order->items()->create([
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'price' => 40000,
            'quantity' => 1,
            'fulfillment_date' => $date,
            'fulfillment_slot' => '13:00',
        ]);

        // Customer visits payment page for expired order
        $response = $this->get(route('payment.show', $order->order_number));

        $response->assertStatus(200);
        $response->assertSee('Batas Waktu Pembayaran Habis');

        $order->refresh();
        $this->assertEquals(InternalOrderStatus::CANCELLED->value, $order->internal_status);

        $variant->refresh();
        $this->assertEquals(6, $variant->stock);

        $slotQuota->refresh();
        $this->assertEquals(0, $slotQuota->booked);
    }
}
