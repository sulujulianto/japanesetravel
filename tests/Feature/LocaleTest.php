<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_uses_accept_language_id(): void
    {
        $response = $this->get('/', [
            'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
        ]);

        $response->assertSee('Temukan destinasi Jepang dan oleh-oleh pilihan.');
    }

    public function test_locale_uses_accept_language_en(): void
    {
        $response = $this->get('/', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response->assertSee('Discover Japan destinations and curated souvenirs.');
    }

    public function test_locale_toggle_cookie_overrides_header(): void
    {
        $this->get(route('lang.switch', 'en'))
            ->assertCookie('locale', 'en');

        $response = $this->withCookie('locale', 'en')
            ->get('/', [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ]);

        $response->assertSee('Discover Japan destinations and curated souvenirs.');
    }

    public function test_places_catalog_uses_english_copy(): void
    {
        $response = $this->get('/places', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response
            ->assertOk()
            ->assertSee('Destination catalog')
            ->assertDontSee('Katalog destinasi');
    }

    public function test_shop_uses_english_copy(): void
    {
        $response = $this->get('/shop', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response
            ->assertOk()
            ->assertSee('Curated souvenir catalog')
            ->assertDontSee('Katalog oleh-oleh pilihan');
    }

    public function test_empty_cart_uses_english_copy(): void
    {
        $response = $this->get('/cart', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response
            ->assertOk()
            ->assertSee('Souvenir cart')
            ->assertSee('Your cart has no souvenirs yet')
            ->assertDontSee('Keranjang oleh-oleh');
    }

    public function test_login_uses_english_copy(): void
    {
        $response = $this->get('/login', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response
            ->assertOk()
            ->assertSee('Sign in to Japan Travel')
            ->assertDontSee('Masuk ke Japan Travel');
    }

    public function test_register_uses_english_copy(): void
    {
        $response = $this->get('/register', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response
            ->assertOk()
            ->assertSee('Create an account')
            ->assertDontSee('Buat akun');
    }

    public function test_authenticated_dashboard_uses_english_copy(): void
    {
        $user = User::factory()->create(['username' => 'demo-user']);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard', [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ]);

        $response
            ->assertOk()
            ->assertSee('Hello, demo-user')
            ->assertSee('Account activity')
            ->assertDontSee('Aktivitas akun');
    }

    public function test_profile_uses_english_copy(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile', [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ]);

        $response
            ->assertOk()
            ->assertSee('Profile settings')
            ->assertSee('Profile Information')
            ->assertDontSee('Pengaturan profil');
    }

    public function test_empty_orders_uses_english_copy(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/orders', [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ]);

        $response
            ->assertOk()
            ->assertSee('Order history')
            ->assertSee('Your order history is empty')
            ->assertDontSee('Riwayat pesanan');
    }

    public function test_admin_dashboard_uses_english_copy(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.dashboard'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ]);

        $response
            ->assertOk()
            ->assertSee('Admin Dashboard')
            ->assertSee('Key Metrics')
            ->assertDontSee('Metrik Utama');
    }

    public function test_admin_places_uses_english_copy(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.places.index'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ]);

        $response
            ->assertOk()
            ->assertSee('Manage Destinations')
            ->assertSee('Destination List')
            ->assertDontSee('Daftar Destinasi');
    }

    public function test_admin_souvenirs_uses_english_copy(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.souvenirs.index'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ]);

        $response
            ->assertOk()
            ->assertSee('Manage Souvenirs')
            ->assertSee('Souvenir List')
            ->assertDontSee('Daftar Souvenir');
    }

    public function test_admin_orders_uses_english_copy(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.orders.index'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ]);

        $response
            ->assertOk()
            ->assertSee('Order List')
            ->assertSee('Order Filters')
            ->assertDontSee('Filter Pesanan');
    }

    public function test_admin_low_stock_uses_english_copy(): void
    {
        $response = $this
            ->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.inventory.low-stock'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ]);

        $response
            ->assertOk()
            ->assertSee('Low Stock')
            ->assertSee('Stock Monitoring Threshold')
            ->assertDontSee('Batas Pemantauan Stok');
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }
}
