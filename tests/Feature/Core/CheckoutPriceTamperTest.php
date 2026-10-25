<?php

namespace Tests\Feature\Core;

use App\Contracts\PaymentGateway;
use App\Enums\InternalOrderStatus;
use App\Models\Collection;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\SlotQuota;
use App\Models\Variant;
use App\Services\FakeGateway;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPriceTamperTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_input_price_is_ignored_and_computed_strictly_from_db(): void
    {
        $this->app->bind(PaymentGateway::class, fn () => new FakeGateway());

        $date = Carbon::now('Asia/Makassar')->addDays(2)->format('Y-m-d');
        SlotQuota::create([
            'date' => $date,
            'time_slot' => '10:00',
            'capacity' => 5,
            'booked' => 0,
        ]);

        $zone = DeliveryZone::create([
            'name' => 'Makassar Kota',
            'fee' => 15000,
            'is_active' => true,
        ]);

        $collection = Collection::create([
            'name' => 'Cake',
            'slug' => 'cake-' . uniqid(),
        ]);

        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Strawberry Cream Cake',
            'slug' => 'scc-' . uniqid(),
            'portion_info' => '6-8 porsi',
            'lead_time_days' => 1,
        ]);

        // Real DB price is Rp 185.000
        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => '16 cm',
            'sku' => 'SCC-16-' . uniqid(),
            'price' => 185000,
            'stock' => 5,
        ]);

        // Malicious client tampers with session price (e.g. injects price = 1000)
        session()->put('cart', [
            "{$variant->id}_{$date}_10:00" => [
                'variant_id' => $variant->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variant_name' => $variant->name,
                'price' => 1000, // Tampered price
                'quantity' => 1,
                'greeting_card' => 'Selamat Ulang Tahun',
                'fulfillment_date' => $date,
                'fulfillment_slot' => '10:00',
            ],
        ]);

        // Client submits checkout form trying to post tampered subtotal / shipping
        $checkoutData = [
            'customer_name' => 'Dewi Lestari',
            'customer_phone' => '081234567890',
            'customer_email' => 'dewi@example.com',
            'delivery_type' => 'delivery',
            'delivery_zone_id' => $zone->id,
            'delivery_address' => 'Jl. Pengayoman No. 10, Makassar',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
            'subtotal' => 1000, // Tampered! Must be ignored by server
            'shipping_fee' => 100, // Tampered! Must be ignored by server
            'total_amount' => 1100, // Tampered! Must be ignored by server
        ];

        $response = $this->post(route('checkout.store'), $checkoutData);

        $order = Order::where('customer_phone', '081234567890')->first();
        $this->assertNotNull($order);

        // Expected: Subtotal (185.000) + DeliveryZone Fee (15.000) = 200.000
        $this->assertEquals(185000, $order->subtotal);
        $this->assertEquals(15000, $order->shipping_fee);
        $this->assertEquals(200000, $order->total_amount);

        // Redirects to payment page
        $response->assertRedirect(route('payment.show', ['orderNumber' => $order->order_number]));
    }

    public function test_second_request_for_filled_slot_fails_atomically(): void
    {
        $this->app->bind(PaymentGateway::class, fn () => new FakeGateway());

        $date = Carbon::now('Asia/Makassar')->addDays(2)->format('Y-m-d');

        // Slot with capacity = 2, already booked = 1
        $slot = SlotQuota::create([
            'date' => $date,
            'time_slot' => '16:00',
            'capacity' => 2,
            'booked' => 1,
        ]);

        $collection = Collection::create([
            'name' => 'Cake',
            'slug' => 'cake-' . uniqid(),
        ]);

        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Kue Red Velvet',
            'slug' => 'krv-' . uniqid(),
            'portion_info' => '6-8 porsi',
            'lead_time_days' => 1,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => '16 cm',
            'sku' => 'KRV-16-' . uniqid(),
            'price' => 100000,
            'stock' => 10,
        ]);

        // Customer 1 books 1 item -> fills the slot to 2/2
        session()->put('cart', [
            'item_1' => [
                'variant_id' => $variant->id,
                'quantity' => 1,
                'fulfillment_date' => $date,
                'fulfillment_slot' => '16:00',
            ],
        ]);

        $res1 = $this->post(route('checkout.store'), [
            'customer_name' => 'Customer Satu',
            'customer_phone' => '081111111111',
            'delivery_type' => 'pickup',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '16:00',
        ]);
        $res1->assertSessionHasNoErrors();

        $slot->refresh();
        $this->assertEquals(2, $slot->booked); // Slot now at max capacity (2/2)

        // Customer 2 attempts to checkout for the same filled slot
        session()->put('cart', [
            'item_2' => [
                'variant_id' => $variant->id,
                'quantity' => 1,
                'fulfillment_date' => $date,
                'fulfillment_slot' => '16:00',
            ],
        ]);

        $res2 = $this->from(route('checkout.index'))->post(route('checkout.store'), [
            'customer_name' => 'Customer Dua',
            'customer_phone' => '082222222222',
            'delivery_type' => 'pickup',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '16:00',
        ]);

        // Second request must fail with error
        $res2->assertSessionHasErrors('fulfillment');
        $this->assertEquals(1, Order::where('fulfillment_date', $date)->where('fulfillment_slot', '16:00')->count());
        $slot->refresh();
        $this->assertEquals(2, $slot->booked); // Did NOT exceed 2
    }

    public function test_full_order_and_payment_and_tracking_flow(): void
    {
        $this->app->bind(PaymentGateway::class, fn () => new FakeGateway());

        $date = Carbon::now('Asia/Makassar')->addDays(2)->format('Y-m-d');
        SlotQuota::create([
            'date' => $date,
            'time_slot' => '13:00',
            'capacity' => 5,
            'booked' => 0,
        ]);

        $promo = PromoCode::create([
            'code' => 'KLIKMANIS',
            'type' => 'percentage',
            'value' => 10,
            'max_discount' => 50000,
            'is_active' => true,
        ]);

        $collection = Collection::create([
            'name' => 'Cake',
            'slug' => 'cake-' . uniqid(),
        ]);

        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Choco Mousse',
            'slug' => 'cm-' . uniqid(),
            'portion_info' => '6-8 porsi',
            'lead_time_days' => 1,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => 'Slice',
            'sku' => 'CM-SL-' . uniqid(),
            'price' => 150000,
            'stock' => 10,
        ]);

        // 1. Add to cart
        $addRes = $this->post(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
            'greeting_card' => 'Happy Birthday!',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '13:00',
        ]);
        $addRes->assertRedirect(route('cart.index'));
        $this->assertEquals(2, session('cart_count'));

        // 2. View Cart page
        $cartRes = $this->get(route('cart.index'));
        $cartRes->assertStatus(200);
        $cartRes->assertSee('Choco Mousse');
        $cartRes->assertSee('Happy Birthday!');

        // 3. View Checkout page
        $checkoutGetRes = $this->get(route('checkout.index'));
        $checkoutGetRes->assertStatus(200);
        $checkoutGetRes->assertSee('Konfirmasi & Checkout', false);

        // 4. Submit Checkout with promo code
        $checkoutPostRes = $this->post(route('checkout.store'), [
            'customer_name' => 'Maya Kartika',
            'customer_phone' => '081345678912',
            'customer_email' => 'maya@example.com',
            'delivery_type' => 'pickup',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '13:00',
            'promo_code' => 'KLIKMANIS',
        ]);

        $order = Order::where('customer_phone', '081345678912')->first();
        $this->assertNotNull($order);
        // Subtotal: 150.000 * 2 = 300.000. 10% discount = 30.000. Total = 270.000
        $this->assertEquals(300000, $order->subtotal);
        $this->assertEquals(30000, $order->discount_amount);
        $this->assertEquals(270000, $order->total_amount);

        $checkoutPostRes->assertRedirect(route('payment.show', $order->order_number));

        // 5. View Payment page
        $paymentGetRes = $this->get(route('payment.show', $order->order_number));
        $paymentGetRes->assertStatus(200);
        $paymentGetRes->assertSee('#' . $order->order_number);
        $paymentGetRes->assertSee('Rp270.000');
        $paymentGetRes->assertSee('Saya Sudah Bayar');

        // 6. Check Payment Status via "Saya Sudah Bayar" API
        $statusRes = $this->postJson(route('payment.status', $order->order_number));
        $statusRes->assertStatus(200);
        $statusRes->assertJson([
            'status' => 'paid',
            'redirect_url' => route('track.show', $order->order_number),
        ]);

        $order->refresh();
        $this->assertEquals(InternalOrderStatus::PAYMENT_VERIFIED->value, $order->internal_status);

        // 7. View Track page
        $trackRes = $this->get(route('track.show', $order->order_number));
        $trackRes->assertStatus(200);
        $trackRes->assertSee('#' . $order->order_number);
        $trackRes->assertSee('Choco Mousse');
        $trackRes->assertSee('Chat WhatsApp Toko');
    }
}
