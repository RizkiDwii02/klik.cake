<?php

namespace Tests\Feature\Shop;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang');
        $response->assertSee('Akses Pelanggan', false);
        $response->assertSee('Masuk ke Akun');
        $response->assertSee('Terhubung untuk Administrator Toko');
    }

    public function test_customer_can_login_and_is_redirected_to_account_dashboard(): void
    {
        $user = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'customer',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'siti@example.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('account.dashboard'));

        $accountResponse = $this->get(route('account.dashboard'));
        $accountResponse->assertStatus(200);
        $accountResponse->assertSee('Siti Nurhaliza');
        $accountResponse->assertSee('Pelanggan');
    }

    public function test_admin_can_login_via_user_login_menu_and_is_redirected_to_admin_panel(): void
    {
        $admin = User::create([
            'name' => 'Pinky Cake Admin',
            'email' => 'admin@pinkycake.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'admin@pinkycake.com',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $this->assertTrue(Auth::user()->isAdmin());
        $response->assertRedirect('/admin');

        // Admin can now directly access /admin because same web session is used
        $adminDashboard = $this->actingAs($admin)->get('/admin');
        $adminDashboard->assertStatus(200);
    }

    public function test_customer_cannot_login_with_invalid_password(): void
    {
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => bcrypt('validpassword'),
            'role' => 'customer',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'budi@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_customer_can_register_new_account(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Andi Muhammad',
            'email' => 'andi@makassar.local',
            'password' => 'makassar2026',
            'password_confirmation' => 'makassar2026',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'andi@makassar.local',
            'role' => 'customer',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('account.dashboard'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Fadli',
            'email' => 'fadli@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_layout_shows_login_button_when_guest(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Masuk');
    }

    public function test_layout_shows_user_menu_and_admin_link_when_admin_logged_in(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@klikcake.local',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Super Admin');
        $response->assertSee('Admin');
        $response->assertSee(url('/admin'));
    }
}
