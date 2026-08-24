<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_orders_are_rendered_by_inertia_with_explicit_contract(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin, 'admin')
            ->withCookie('locale', 'id')
            ->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Orders/Index')
            ->where('copy.title', 'Daftar Pesanan')
            ->where('routes.orders', '/admin/orders')
            ->where('filters.search', '')
            ->where('filters.status', '')
            ->has('options.orderStatuses', 4)
            ->has('options.paymentStatuses', 6)
            ->has('orders.data', 0)
            ->where('orders.pagination.currentPage', 1)
            ->where('orders.pagination.total', 0)
        );
    }

    public function test_orders_are_serialized_without_exposing_models(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['username' => 'order-customer']);
        $order = $this->createOrder($customer, 'processing', '2026-06-09 14:30:00', 'paid');

        $response = $this
            ->actingAs($admin, 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.orders.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.id', $order->id)
            ->where('orders.data.0.reference', '#ORDER-'.$order->id)
            ->where('orders.data.0.customer.username', 'order-customer')
            ->where('orders.data.0.date', 'Jun 9, 2026')
            ->where('orders.data.0.total', 'IDR 250,000')
            ->where('orders.data.0.payment.status', 'paid')
            ->where('orders.data.0.status.value', 'processing')
            ->where('orders.data.0.url', '/admin/orders/'.$order->id)
            ->missing('orders.data.0.user_id')
            ->missing('orders.data.0.created_at')
            ->missing('orders.data.0.payment.payload_json')
        );
    }

    public function test_order_filters_are_applied_and_reflected_in_props(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matchingCustomer = User::factory()->create(['email' => 'matching@example.test']);
        $otherCustomer = User::factory()->create(['email' => 'other@example.test']);
        $matchingOrder = $this->createOrder($matchingCustomer, 'processing', '2026-06-10 10:00:00', 'paid');
        $this->createOrder($otherCustomer, 'pending', '2026-05-01 10:00:00', 'pending');

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.orders.index', [
                'q' => 'matching@example.test',
                'status' => 'processing',
                'payment_status' => 'paid',
                'date_from' => '2026-06-01',
                'date_to' => '2026-06-30',
            ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', 'matching@example.test')
            ->where('filters.status', 'processing')
            ->where('filters.paymentStatus', 'paid')
            ->where('filters.dateFrom', '2026-06-01')
            ->where('filters.dateTo', '2026-06-30')
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $matchingOrder->id)
        );
    }

    public function test_pagination_links_preserve_active_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();

        foreach (range(1, 16) as $day) {
            $this->createOrder($customer, 'pending', sprintf('2026-06-%02d 10:00:00', $day));
        }

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.orders.index', ['status' => 'pending']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('orders.data', 15)
            ->where('orders.pagination.currentPage', 1)
            ->where('orders.pagination.lastPage', 2)
            ->where('orders.pagination.total', 16)
            ->where('orders.pagination.nextUrl', '/admin/orders?status=pending&page=2')
            ->where('orders.pagination.pages.1.url', '/admin/orders?status=pending&page=2')
        );
    }

    public function test_invalid_filter_values_are_ignored_deterministically(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.orders.index', [
                'status' => 'unknown',
                'payment_status' => 'unknown',
                'date_from' => 'not-a-date',
                'date_to' => '2026-01-01',
            ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('filters.status', '')
            ->where('filters.paymentStatus', '')
            ->where('filters.dateFrom', '')
            ->where('filters.dateTo', '2026-01-01')
        );
    }

    private function createOrder(User $customer, string $status, string $createdAt, ?string $paymentStatus = null): Order
    {
        $order = Order::create([
            'user_id' => $customer->id,
            'total_price' => 250000,
            'status' => $status,
            'note' => null,
        ]);
        $order->forceFill(['created_at' => CarbonImmutable::parse($createdAt)])->save();

        if ($paymentStatus !== null) {
            Payment::create([
                'order_id' => $order->id,
                'provider' => 'midtrans',
                'provider_ref' => 'ORDER-'.$order->id,
                'status' => $paymentStatus,
                'amount' => 250000,
                'currency' => 'IDR',
                'payload_json' => [],
                'paid_at' => $paymentStatus === 'paid' ? CarbonImmutable::parse($createdAt) : null,
            ]);
        }

        return $order;
    }
}
