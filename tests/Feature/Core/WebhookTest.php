<?php

namespace Tests\Feature\Core;

use App\Enums\InternalOrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SlotQuota;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected string $serverKey = 'SB-Mid-server-testkey123456';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('shop.midtrans.server_key', $this->serverKey);
    }

    protected function createSampleOrder(int $totalAmount = 185000): Order
    {
        $collection = Collection::create([
            'name' => 'Cake',
            'slug' => 'cake-' . uniqid(),
        ]);

        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Strawberry Cake',
            'slug' => 'sc-' . uniqid(),
            'portion_info' => '6-8 porsi',
            'lead_time_days' => 1,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => '16 cm',
            'sku' => 'SC-16-' . uniqid(),
            'price' => $totalAmount,
            'stock' => 10,
        ]);

        $order = Order::create([
            'order_number' => 'KC-TEST-' . uniqid(),
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'customer_email' => 'budi@test.com',
            'delivery_type' => 'pickup',
            'delivery_address' => 'Ambil di Toko',
            'subtotal' => $totalAmount,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => 'Diterima',
            'fulfillment_date' => now()->addDays(2)->format('Y-m-d'),
            'fulfillment_slot' => '10:00',
            'expires_at' => now()->addHour(),
        ]);

        $order->items()->create([
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'price' => $variant->price,
            'quantity' => 1,
            'fulfillment_date' => $order->fulfillment_date,
            'fulfillment_slot' => $order->fulfillment_slot,
        ]);

        return $order;
    }

    public function test_fake_signature_is_rejected_with_401(): void
    {
        $order = $this->createSampleOrder(185000);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => '185000.00',
            'signature_key' => 'invalid-fake-signature-hash',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'mid-tx-001',
        ];

        $response = $this->postJson(route('webhook.midtrans'), $payload);

        $response->assertStatus(401);
        $order->refresh();
        $this->assertEquals(InternalOrderStatus::ORDER_PLACED->value, $order->internal_status);
        $this->assertDatabaseMissing('payments', ['provider_transaction_id' => 'mid-tx-001']);
    }

    public function test_gross_amount_mismatch_is_rejected_with_401(): void
    {
        $order = $this->createSampleOrder(185000);

        $tamperedAmount = '50000.00';
        $signature = hash('sha512', $order->order_number . '200' . $tamperedAmount . $this->serverKey);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => $tamperedAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'mid-tx-002',
        ];

        $response = $this->postJson(route('webhook.midtrans'), $payload);

        $response->assertStatus(401);
        $order->refresh();
        $this->assertEquals(InternalOrderStatus::ORDER_PLACED->value, $order->internal_status);
        $this->assertDatabaseMissing('payments', ['provider_transaction_id' => 'mid-tx-002']);
    }

    public function test_valid_settlement_notification_transitions_order_to_payment_verified(): void
    {
        $order = $this->createSampleOrder(185000);
        $grossAmountStr = '185000.00';
        $signature = hash('sha512', $order->order_number . '200' . $grossAmountStr . $this->serverKey);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => $grossAmountStr,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'mid-tx-003',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson(route('webhook.midtrans'), $payload);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(InternalOrderStatus::PAYMENT_VERIFIED->value, $order->internal_status);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(PaymentStatus::PAID->value, $payment->status);
        $this->assertEquals(185000, $payment->gross_amount);
        $this->assertNotNull($payment->paid_at);

        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'to_status' => InternalOrderStatus::PAYMENT_VERIFIED->value,
        ]);
    }

    public function test_duplicate_notification_is_idempotent(): void
    {
        $order = $this->createSampleOrder(185000);
        $grossAmountStr = '185000.00';
        $signature = hash('sha512', $order->order_number . '200' . $grossAmountStr . $this->serverKey);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => $grossAmountStr,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'mid-tx-004',
            'fraud_status' => 'accept',
        ];

        // 1st notification
        $res1 = $this->postJson(route('webhook.midtrans'), $payload);
        $res1->assertStatus(200);

        $logCountBefore = $order->statusLogs()->count();

        // 2nd duplicate notification
        $res2 = $this->postJson(route('webhook.midtrans'), $payload);
        $res2->assertStatus(200);

        // Status log count must not increment again
        $this->assertEquals($logCountBefore, $order->statusLogs()->count());
    }

    public function test_expired_notification_cancels_order_and_releases_slot_and_stock(): void
    {
        $date = now()->addDays(2)->format('Y-m-d');
        $slotQuota = SlotQuota::create([
            'date' => $date,
            'time_slot' => '10:00',
            'capacity' => 5,
            'booked' => 2,
        ]);

        $collection = Collection::create([
            'name' => 'Roti',
            'slug' => 'roti-' . uniqid(),
        ]);

        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Roti Manis',
            'slug' => 'rm-' . uniqid(),
            'portion_info' => '1 orang',
            'lead_time_days' => 1,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => 'Cokelat',
            'sku' => 'RM-CK-' . uniqid(),
            'price' => 50000,
            'stock' => 8,
        ]);

        $order = Order::create([
            'order_number' => 'KC-TEST-' . uniqid(),
            'customer_name' => 'Siti Aminah',
            'customer_phone' => '082111222333',
            'delivery_type' => 'pickup',
            'delivery_address' => 'Ambil di Toko',
            'subtotal' => 50000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 50000,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => 'Diterima',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
            'expires_at' => now()->addHour(),
        ]);

        $order->items()->create([
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'price' => 50000,
            'quantity' => 2,
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
        ]);

        $grossAmountStr = '50000.00';
        $signature = hash('sha512', $order->order_number . '200' . $grossAmountStr . $this->serverKey);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => $grossAmountStr,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'mid-tx-005',
        ];

        $res = $this->postJson(route('webhook.midtrans'), $payload);
        $res->assertStatus(200);

        $order->refresh();
        $this->assertEquals(InternalOrderStatus::CANCELLED->value, $order->internal_status);

        // Stock restored (+2)
        $variant->refresh();
        $this->assertEquals(10, $variant->stock);

        // Booked slot released (-2)
        $slotQuota->refresh();
        $this->assertEquals(0, $slotQuota->booked);
    }
}
