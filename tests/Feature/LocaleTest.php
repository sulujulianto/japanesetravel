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
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_uses_accept_language_id(): void
    {
        $response = $this->get('/', [
            'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
        ]);

        $response
            ->assertSee('Temukan destinasi Jepang dan oleh-oleh pilihan.')
            ->assertSee('Dibuat dengan Laravel, Vue, dan Inertia.')
            ->assertDontSee('❤️')
            ->assertDontSee('⛩️');
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

    public function test_order_detail_pluralizes_item_count_for_supported_locales(): void
    {
        [$user, $order] = $this->createOrderForFormatting();

        $this->actingAs($user)
            ->get(route('orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertSee('1 item');

        $this->actingAs($user)
            ->get(route('orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertSee('1 item')
            ->assertDontSee('1 items');

        $this->addOrderItem($order);

        $this->actingAs($user)
            ->get(route('orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertSee('2 item');

        $this->actingAs($user)
            ->get(route('orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertSee('2 items');
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
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard/Index')
                ->where('copy.title', 'Admin Dashboard')
                ->where('copy.metricsTitle', 'Key Metrics')
            );
    }

    public function test_admin_dashboard_formats_revenue_for_indonesian_locale(): void
    {
        $this->createOrderForFormatting();
        Cache::forget('admin:dashboard:metrics');

        $this->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.dashboard'), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'id')
                ->where('metrics.0.value', 'Rp1.234.567')
            );
    }

    public function test_admin_dashboard_formats_revenue_for_english_locale(): void
    {
        $this->createOrderForFormatting();
        Cache::forget('admin:dashboard:metrics');

        $this->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.dashboard'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'en')
                ->where('metrics.0.value', 'IDR 1,234,567')
            );
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
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Index')
                ->where('locale', 'id')
                ->where('orders.data.0.total', 'Rp1.234.567')
                ->where('orders.data.0.date', '9 Jun 2026')
            );

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Show')
                ->where('locale', 'id')
                ->where('order.total', 'Rp1.234.567')
                ->where('order.items.0.unitPrice', 'Rp617.284')
                ->where('order.createdAt', '9 Jun 2026, 14.30')
            );

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Index')
                ->where('locale', 'en')
                ->where('orders.data.0.total', 'IDR 1,234,567')
                ->where('orders.data.0.date', 'Jun 9, 2026')
            );

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order), [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            ])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Show')
                ->where('locale', 'en')
                ->where('order.total', 'IDR 1,234,567')
                ->where('order.items.0.unitPrice', 'IDR 617,284')
                ->where('order.createdAt', 'Jun 9, 2026, 2:30 PM')
            );
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
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Places/Index')
                ->where('locale', 'en')
                ->where('copy.title', 'Manage Destinations')
                ->where('copy.resultsTitle', 'Destination List')
            );
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
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Souvenirs/Index')
                ->where('locale', 'en')
                ->where('copy.title', 'Manage Souvenirs')
                ->where('copy.resultsTitle', 'Souvenir List')
            );
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
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Inventory/LowStock')
                ->where('locale', 'en')
                ->where('copy.title', 'Low Stock')
                ->where('copy.filterTitle', 'Stock Monitoring Threshold')
            );
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

    private function addOrderItem(Order $order): void
    {
        $souvenir = Souvenir::factory()->create([
            'price' => 100000,
            'stock' => 5,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'souvenir_id' => $souvenir->id,
            'quantity' => 1,
            'price' => 100000,
            'product_name' => 'Second Formatting Test Souvenir',
            'product_price' => 100000,
            'product_image' => null,
        ]);
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
