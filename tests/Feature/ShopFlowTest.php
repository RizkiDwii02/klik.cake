<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShopFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertEquals(
            'klikcake_test',
            DB::connection()->getDatabaseName(),
            'SAFETY ERROR: Tests must only run on klikcake_test'
        );

        $this->seed();
    }

    public function test_homepage_loads_successfully_with_target_elements(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Klik, dipanggang,');
        $response->assertSee('sampai.');
        $response->assertSee('Fresh Bake hari ini');
        $response->assertSee('Strawberry Cream Cake');
        $response->assertSee('Cara pesan');
    }

    public function test_catalog_loads_with_filters_and_pagination(): void
    {
        $response = $this->get(route('catalog.index'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Menu');
        $response->assertSee('Harga');
        $response->assertSee('Acara');

        // Test category filter for Cake
        $catResponse = $this->get(route('catalog.category', 'cake'));
        $catResponse->assertStatus(200);
        $catResponse->assertSee('Cake');
        $catResponse->assertSee('Strawberry Cream Cake');

        // Test price filter
        $priceResponse = $this->get(route('catalog.index', ['harga' => 'over_150k']));
        $priceResponse->assertStatus(200);
        $priceResponse->assertSee('Strawberry Cream Cake');
    }

    public function test_product_detail_page_loads_with_strawberry_cream_cake(): void
    {
        $response = $this->get(route('product.show', 'strawberry-cream-cake'));

        $response->assertStatus(200);
        $response->assertSee('Strawberry Cream Cake');
        $response->assertSee('Rp185.000');
        $response->assertSee('16 cm');
        $response->assertSee('20 cm');
        $response->assertSee('Ucapan kartu (maks 40 karakter)');
        $response->assertSee('susu, telur, gluten');
        $response->assertSee('Simpan 2 hari di kulkas');
        $response->assertSee('Tambah ke Keranjang');
    }

    public function test_api_slots_endpoint_returns_valid_json(): void
    {
        $targetDate = Carbon::now('Asia/Makassar')->addDays(2)->toDateString();
        $response = $this->getJson(route('api.slots', ['tanggal' => $targetDate, 'lead_time' => 1]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'date',
            'slots' => [
                '*' => ['time_slot', 'label', 'is_available', 'capacity', 'booked', 'remaining'],
            ],
        ]);
    }

    public function test_four_step_customer_flow_from_homepage_to_cart(): void
    {
        // Step 1: Homepage
        $step1 = $this->get(route('home'));
        $step1->assertStatus(200);

        // Step 2: Catalog
        $step2 = $this->get(route('catalog.index'));
        $step2->assertStatus(200);

        // Step 3: Product Detail
        $product = Product::where('slug', 'strawberry-cream-cake')->firstOrFail();
        $variant = $product->variants->first();
        $step3 = $this->get(route('product.show', $product->slug));
        $step3->assertStatus(200);

        // Step 4: Add to Cart (Session)
        $targetDate = Carbon::now('Asia/Makassar')->addDays(2)->toDateString();
        $step4 = $this->post(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
            'greeting_card' => 'Selamat Ulang Tahun!',
            'fulfillment_date' => $targetDate,
            'fulfillment_slot' => '10:00',
        ]);

        $step4->assertRedirect();
        $step4->assertSessionHas('success');
        $step4->assertSessionHas('cart_count', 1);

        $this->assertEquals(1, session('cart_count'));
    }
}
