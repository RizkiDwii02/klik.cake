<?php

namespace Tests\Feature\Admin;

use App\Contracts\PaymentGateway;
use App\Enums\CustomerOrderStatus;
use App\Enums\InternalOrderStatus;
use App\Models\Collection;
use App\Models\Order;
use App\Models\Product;
use App\Models\SlotQuota;
use App\Models\User;
use App\Models\Variant;
use App\Services\FakeGateway;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->bind(PaymentGateway::class, fn () => new FakeGateway());
    }

    public function test_admin_cannot_be_accessed_without_login(): void
    {
        // Guests cannot access dashboard
        $resDashboard = $this->get('/admin');
        $resDashboard->assertRedirect('/admin/login');

        // Guests cannot access orders list
        $resOrders = $this->get('/admin/orders');
        $resOrders->assertRedirect('/admin/login');

        // Guests cannot access Fresh Bake calendar
        $resCalendar = $this->get('/admin/fresh-bake-calendar');
        $resCalendar->assertRedirect('/admin/login');

        // Login page itself is accessible
        $resLogin = $this->get('/admin/login');
        $resLogin->assertStatus(200);
    }

    public function test_authenticated_admin_can_access_dashboard_and_resources(): void
    {
        $admin = User::create([
            'name' => 'Admin Pinky Cake',
            'email' => 'admin@klikcake.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200)
            ->assertSee('Pesanan Baru')
            ->assertSee('Pesanan Hari Ini')
            ->assertSee('Stok Menipis');

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/fresh-bake-calendar')
            ->assertStatus(200)
            ->assertSee('Kalender Kuota Fresh Bake');
    }

    public function test_changing_status_in_admin_immediately_reflects_on_customer_track_page(): void
    {
        $date = Carbon::now('Asia/Makassar')->addDays(2)->format('Y-m-d');

        $collection = Collection::create(['name' => 'Cake', 'slug' => 'cake-test']);
        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Strawberry Cake',
            'slug' => 'sc-track',
            'lead_time_days' => 1,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => '16 cm',
            'sku' => 'SC-16-TRK',
            'price' => 185000,
            'stock' => 5,
        ]);

        $order = Order::create([
            'order_number' => 'KC-TRK-001',
            'customer_name' => 'Fadli Rahman',
            'customer_phone' => '081299887766',
            'delivery_type' => 'pickup',
            'delivery_address' => 'Ambil di Toko',
            'subtotal' => 185000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 185000,
            'internal_status' => InternalOrderStatus::ORDER_PLACED->value,
            'customer_status' => CustomerOrderStatus::RECEIVED->value,
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
            'expires_at' => now()->addHour(),
        ]);

        $order->items()->create([
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'price' => 185000,
            'quantity' => 1,
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
        ]);

        // Customer views track page: initial status is "Diterima"
        $trackRes1 = $this->get(route('track.show', $order->order_number));
        $trackRes1->assertStatus(200);
        $trackRes1->assertSee('Diterima');

        // Admin updates status sequentially to BAKING ("Dipanggang")
        $order->updateInternalStatus(
            InternalOrderStatus::BAKING,
            'Pesanan mulai dipanggang di dapur Pinky Cake',
            'Admin'
        );

        $order->refresh();
        $this->assertEquals(CustomerOrderStatus::BAKING->value, $order->customer_status);

        // Customer immediately sees "Dipanggang"
        $trackRes2 = $this->get(route('track.show', $order->order_number));
        $trackRes2->assertStatus(200);
        $trackRes2->assertSee('Dipanggang');

        // Admin updates status to COMPLETED ("Selesai")
        $order->updateInternalStatus(
            InternalOrderStatus::COMPLETED,
            'Pesanan telah diambil oleh pembeli di outlet',
            'Admin'
        );

        $order->refresh();
        $this->assertEquals(CustomerOrderStatus::COMPLETED->value, $order->customer_status);

        // Customer immediately sees "Selesai"
        $trackRes3 = $this->get(route('track.show', $order->order_number));
        $trackRes3->assertStatus(200);
        $trackRes3->assertSee('Selesai');
    }

    public function test_decreasing_slot_capacity_makes_it_unavailable_in_shop(): void
    {
        $date = Carbon::now('Asia/Makassar')->addDays(2)->format('Y-m-d');
        $slotService = app(SlotAvailabilityService::class);

        // Initial state: Capacity 5, Booked 3 -> 2 slots available
        $slotQuota = SlotQuota::create([
            'date' => $date,
            'time_slot' => '10:00',
            'capacity' => 5,
            'booked' => 3,
        ]);

        $slots = $slotService->getAvailableSlots(Carbon::parse($date), 1, 1);
        $this->assertTrue($slots['10:00']['is_available']);

        // Admin decreases capacity in Fresh Bake Calendar to 3 (matching booked count)
        $slotQuota->update(['capacity' => 3]);

        // Now booked (3) >= capacity (3) -> Slot is FULL and cannot be selected
        $slotsAfter = $slotService->getAvailableSlots(Carbon::parse($date), 1, 1);
        $this->assertFalse($slotsAfter['10:00']['is_available']);

        // Customer attempting to order this slot via Checkout will be rejected
        $collection = Collection::create(['name' => 'Pastry', 'slug' => 'pastry-test']);
        $product = Product::create([
            'collection_id' => $collection->id,
            'name' => 'Croissant',
            'slug' => 'croissant-slot-test',
            'lead_time_days' => 1,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'name' => 'Butter',
            'sku' => 'CR-BTR-1',
            'price' => 25000,
            'stock' => 10,
        ]);

        session()->put('cart', [
            'item_1' => [
                'variant_id' => $variant->id,
                'quantity' => 1,
                'fulfillment_date' => $date,
                'fulfillment_slot' => '10:00',
            ],
        ]);

        $res = $this->from(route('checkout.index'))->post(route('checkout.store'), [
            'customer_name' => 'Rudyanto',
            'customer_phone' => '081234112233',
            'delivery_type' => 'pickup',
            'fulfillment_date' => $date,
            'fulfillment_slot' => '10:00',
        ]);

        $res->assertSessionHasErrors('fulfillment');
    }
}
