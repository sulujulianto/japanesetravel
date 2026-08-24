<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_is_rendered_by_inertia_with_explicit_contract(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin, 'admin')
            ->withCookie('locale', 'id')
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->where('auth.admin.username', $admin->username)
            ->where('copy.title', 'Dashboard Admin')
            ->where('routes.dashboard', '/admin')
            ->where('routes.orders', '/admin/orders')
            ->has('metrics', 4)
            ->where('metrics.0.key', 'revenue')
            ->where('metrics.0.value', 'Rp0')
            ->has('recentOrders', 0)
            ->has('lowStockItems', 0)
        );
    }

    public function test_dashboard_serializes_operational_data_without_exposing_models(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['username' => 'dashboard-customer']);
        $souvenir = Souvenir::factory()->create(['stock' => 3]);
        $order = Order::create([
            'user_id' => $customer->id,
            'total_price' => 250000,
            'status' => 'processing',
            'note' => null,
        ]);
        Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'DASHBOARD-'.$order->id,
            'status' => 'paid',
            'amount' => 250000,
            'currency' => 'IDR',
            'payload_json' => [],
            'paid_at' => now(),
        ]);
        Cache::forget('admin:dashboard:metrics');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('recentOrders.0.id', $order->id)
            ->where('recentOrders.0.customer.username', 'dashboard-customer')
            ->where('recentOrders.0.payment.status', 'paid')
            ->where('recentOrders.0.status.value', 'processing')
            ->where('recentOrders.0.url', '/admin/orders/'.$order->id)
            ->where('lowStockItems.0.id', $souvenir->id)
            ->where('lowStockItems.0.stock', 3)
            ->missing('recentOrders.0.user_id')
            ->missing('recentOrders.0.created_at')
            ->missing('lowStockItems.0.description')
        );
    }
}
