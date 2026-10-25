<?php

namespace Tests\Feature\Core;

use App\Contracts\PaymentGateway;
use App\Enums\CustomerOrderStatus;
use App\Enums\InternalOrderStatus;
use App\Models\Collection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertEquals(
            'klikcake_test',
            DB::connection()->getDatabaseName(),
            'SAFETY ERROR: Must run only on klikcake_test'
        );
    }

    public function test_order_creation_with_1_hour_expiration_and_greeting_card(): void
    {
        $collection = Collection::create([
            'name' => 'Cake',
            'slug' => 'cake-test',
        ]);

        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Strawberry Cream Cake',
            'slug' => 'scc-test',
            'portion_info' => 'Porsi 6–8 orang',
            'lead_time_days' => 1,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => '16 cm',
            'sku' => 'SCC-TEST-16',
            'price' => 185000,
            'stock' => 10,
        ]);

        $order = Order::create([
            'order_number' => 'KC-20261023-0001',
            'customer_name' => 'Nurul Makassar',
            'customer_phone' => '081234567890',
            'delivery_type' => 'delivery',
            'delivery_address' => 'Jl. Boulevard No. 12, Panakkukang',
            'subtotal' => 185000,
            'shipping_fee' => 15000,
            'discount_amount' => 0,
            'total_amount' => 200000,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => CustomerOrderStatus::RECEIVED->value,
            'fulfillment_date' => Carbon::now('Asia/Makassar')->addDay()->toDateString(),
            'fulfillment_slot' => '13:00',
            'expires_at' => Carbon::now('Asia/Makassar')->addHour(),
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'price' => $variant->price,
            'quantity' => 1,
            'greeting_card' => 'Selamat Ulang Tahun Ibu Tercinta!', // under 40 chars
            'fulfillment_date' => $order->fulfillment_date,
            'fulfillment_slot' => $order->fulfillment_slot,
        ]);

        $this->assertDatabaseHas('orders', ['order_number' => 'KC-20261023-0001']);
        $this->assertDatabaseHas('order_items', ['greeting_card' => 'Selamat Ulang Tahun Ibu Tercinta!']);
        $this->assertFalse($order->isExpired());
    }

    public function test_internal_status_transition_maps_to_customer_status(): void
    {
        $order = Order::create([
            'order_number' => 'KC-20261023-0002',
            'customer_name' => 'Andi',
            'customer_phone' => '081299998888',
            'delivery_type' => 'pickup',
            'subtotal' => 185000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 185000,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => CustomerOrderStatus::RECEIVED->value,
            'fulfillment_date' => Carbon::now('Asia/Makassar')->addDay()->toDateString(),
            'fulfillment_slot' => '10:00',
            'expires_at' => Carbon::now('Asia/Makassar')->addHour(),
        ]);

        // Step 1: Internal "Pembayaran terverifikasi" -> Customer "Diterima"
        $order->updateInternalStatus(InternalOrderStatus::PAYMENT_VERIFIED);
        $this->assertEquals(CustomerOrderStatus::RECEIVED->value, $order->customer_status);

        // Step 2: Internal "Dipanggang" -> Customer "Dipanggang"
        $order->updateInternalStatus(InternalOrderStatus::BAKING);
        $this->assertEquals(CustomerOrderStatus::BAKING->value, $order->customer_status);

        // Step 3: Internal "Dikemas" / "Siap diambil" -> Customer "Siap"
        $order->updateInternalStatus(InternalOrderStatus::SHIPPED_OR_READY);
        $this->assertEquals(CustomerOrderStatus::READY->value, $order->customer_status);

        // Step 4: Internal "Selesai" -> Customer "Selesai"
        $order->updateInternalStatus(InternalOrderStatus::COMPLETED);
        $this->assertEquals(CustomerOrderStatus::COMPLETED->value, $order->customer_status);

        // Verify status logs recorded
        $this->assertCount(4, $order->statusLogs);
    }

    public function test_payment_gateway_creates_charge_with_payment_record(): void
    {
        $order = Order::create([
            'order_number' => 'KC-20261023-0003',
            'customer_name' => 'Fadli',
            'customer_phone' => '081122334455',
            'delivery_type' => 'delivery',
            'subtotal' => 185000,
            'shipping_fee' => 15000,
            'discount_amount' => 0,
            'total_amount' => 200000,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => CustomerOrderStatus::RECEIVED->value,
            'fulfillment_date' => Carbon::now('Asia/Makassar')->addDay()->toDateString(),
            'fulfillment_slot' => '16:00',
            'expires_at' => Carbon::now('Asia/Makassar')->addHour(),
        ]);

        $gateway = app(PaymentGateway::class);
        $charge = $gateway->createCharge($order, 'qris');

        $this->assertArrayHasKey('transaction_id', $charge);
        $this->assertEquals(200000, $charge['gross_amount']);
        $this->assertEquals('qris', $charge['payment_method']);
    }
}
