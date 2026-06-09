<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use App\Support\CacheKeys;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    public function test_orders_index_formats_values_for_indonesian_locale(): void
    {
        [$user] = $this->createOrderForFormatting();

        $this->actingAs($user)
            ->get(route('orders.index'), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertSee('Rp1.234.567')
            ->assertSee('9 Jun 2026');
    }

    public function test_orders_index_formats_values_for_english_locale(): void
    {
        [$user] = $this->createOrderForFormatting();

        $this->actingAs($user)
            ->get(route('orders.index'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertSee('IDR 1,234,567')
            ->assertSee('Jun 9, 2026')
            ->assertDontSee('Rp1.234.567');
    }

    public function test_order_detail_formats_payment_for_english_locale(): void
    {
        [$user, $order] = $this->createOrderForFormatting();

        $this->actingAs($user)
            ->get(route('orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertSee('IDR 1,234,567')
            ->assertSee('IDR 617,284')
            ->assertSee('Jun 9, 2026, 2:30 PM')
            ->assertDontSee('Rp1.234.567');
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

    public function test_admin_dashboard_formats_revenue_and_chart_locale_for_indonesian(): void
    {
        $this->createOrderForFormatting();
        Cache::forget('admin:dashboard:metrics');

        $this->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.dashboard'), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertSee('Rp1.234.567')
            ->assertSee('const chartLocale = "id-ID"', false);
    }

    public function test_admin_dashboard_formats_revenue_and_chart_locale_for_english(): void
    {
        $this->createOrderForFormatting();
        Cache::forget('admin:dashboard:metrics');

        $this->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.dashboard'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertSee('IDR 1,234,567')
            ->assertSee('const chartLocale = "en-US"', false)
            ->assertDontSee('Rp1.234.567');
    }

    public function test_admin_order_pages_format_values_for_supported_locales(): void
    {
        [, $order] = $this->createOrderForFormatting();
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertSee('Rp1.234.567')
            ->assertSee('9 Jun 2026');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertSee('Rp1.234.567')
            ->assertSee('Rp617.284')
            ->assertSee('9 Jun 2026, 14.30');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertSee('IDR 1,234,567')
            ->assertSee('Jun 9, 2026')
            ->assertDontSee('Rp1.234.567');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertSee('IDR 1,234,567')
            ->assertSee('IDR 617,284')
            ->assertSee('Jun 9, 2026, 2:30 PM')
            ->assertDontSee('Rp1.234.567');
    }

    public function test_admin_chart_cache_is_isolated_by_locale(): void
    {
        $admin = $this->createAdmin();
        $indonesianPayload = $this->chartPayload('Agu 2026', '9 Agu');
        $englishPayload = $this->chartPayload('Aug 2026', 'Aug 9');

        Cache::put(CacheKeys::adminDashboardCharts('id'), $indonesianPayload, 60);
        Cache::put(CacheKeys::adminDashboardCharts('en'), $englishPayload, 60);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard.charts'), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertJsonPath('revenue.labels.0', 'Agu 2026')
            ->assertJsonPath('orders.labels.0', '9 Agu');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard.charts'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertJsonPath('revenue.labels.0', 'Aug 2026')
            ->assertJsonPath('orders.labels.0', 'Aug 9');
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

    /**
     * @return array{User, Order}
     */
    private function createOrderForFormatting(): array
    {
        $user = User::factory()->create();
        $souvenir = Souvenir::factory()->create([
            'price' => 617283.5,
            'stock' => 5,
        ]);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 1234567,
            'status' => 'processing',
            'note' => 'Formatting test order',
        ]);

        $order->forceFill([
            'created_at' => CarbonImmutable::parse('2026-06-09 14:30:00'),
            'updated_at' => CarbonImmutable::parse('2026-06-09 14:30:00'),
        ])->saveQuietly();

        OrderItem::create([
            'order_id' => $order->id,
            'souvenir_id' => $souvenir->id,
            'quantity' => 2,
            'price' => 617283.5,
            'product_name' => 'Formatting Test Souvenir',
            'product_price' => 617283.5,
            'product_image' => null,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'FORMAT-TEST-'.$order->id,
            'status' => 'paid',
            'amount' => 617283.5,
            'currency' => 'IDR',
            'payload_json' => [],
            'paid_at' => CarbonImmutable::parse('2026-06-09 14:30:00'),
        ]);

        return [$user, $order->fresh()];
    }

    /**
     * @return array{
     *     revenue: array{labels: array<int, string>, series: array<int, float>},
     *     orders: array{labels: array<int, string>, series: array<int, int>},
     *     topSouvenirs: array<int, array{name: string, total: int}>
     * }
     */
    private function chartPayload(string $monthLabel, string $dayLabel): array
    {
        return [
            'revenue' => [
                'labels' => [$monthLabel],
                'series' => [1234567.0],
            ],
            'orders' => [
                'labels' => [$dayLabel],
                'series' => [1],
            ],
            'topSouvenirs' => [],
        ];
    }
}
