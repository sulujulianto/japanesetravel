<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaOrderDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_detail_is_rendered_by_inertia_with_explicit_contract(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'email' => 'detail-customer@example.test',
            'username' => 'detail-customer',
        ]);
        $souvenir = Souvenir::factory()->create([
            'image' => null,
            'name' => ['id' => 'Teh Saat Ini', 'en' => 'Current Tea'],
        ]);
        $order = $this->createOrder($customer, 'processing');
        $item = OrderItem::create([
            'order_id' => $order->id,
            'souvenir_id' => $souvenir->id,
            'quantity' => 2,
            'price' => 125000,
            'product_name' => 'Snapshot Tea',
            'product_price' => 125000,
            'product_image' => 'legacy/orders/snapshot-tea.jpg',
        ]);
        $firstPayment = $this->createPayment($order, 'pending', 'PAYMENT-FIRST', null);
        $latestPayment = $this->createPayment(
            $order,
            'paid',
            'PAYMENT-LATEST',
            CarbonImmutable::parse('2026-06-10 10:15:00')
        );

        $response = $this
            ->actingAs($admin, 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Orders/Show')
            ->where('copy.title', 'Order Details')
            ->where('routes.orders', '/admin/orders')
            ->where('routes.updateOrder', '/admin/orders/'.$order->id)
            ->where('order.id', $order->id)
            ->where('order.reference', '#ORDER-'.$order->id)
            ->where('order.createdAt', 'Jun 9, 2026, 2:30 PM')
            ->where('order.total', 'IDR 250,000')
            ->where('order.customer.username', 'detail-customer')
            ->where('order.customer.email', 'detail-customer@example.test')
            ->where('order.note', 'Customer delivery note')
            ->where('order.adminNote', 'Initial admin note')
            ->where('order.status.value', 'processing')
            ->where('order.latestPayment.status', 'paid')
            ->has('order.items', 1)
            ->where('order.items.0.id', $item->id)
            ->where('order.items.0.name', 'Current Tea')
            ->where('order.items.0.imageUrl', Storage::disk('public')->url('legacy/orders/snapshot-tea.jpg'))
            ->where('order.items.0.quantity', '2')
            ->where('order.items.0.unitPrice', 'IDR 125,000')
            ->where('order.items.0.subtotal', 'IDR 250,000')
            ->has('order.payments', 2)
            ->where('order.payments.0.id', $firstPayment->id)
            ->where('order.payments.0.reference', 'PAYMENT-FIRST')
            ->where('order.payments.0.paidAt', null)
            ->where('order.payments.1.id', $latestPayment->id)
            ->where('order.payments.1.reference', 'PAYMENT-LATEST')
            ->where('order.payments.1.paidAt', 'Jun 10, 2026, 10:15 AM')
            ->has('statusOptions', 3)
            ->where('statusOptions.0.value', 'processing')
            ->where('statusOptions.1.value', 'completed')
            ->where('statusOptions.2.value', 'cancelled')
            ->where('flash.success', null)
            ->where('flash.error', null)
            ->missing('order.user_id')
            ->missing('order.created_at')
            ->missing('order.items.0.souvenir_id')
            ->missing('order.payments.0.payload_json')
        );
    }

    public function test_terminal_orders_only_expose_the_current_status_as_an_update_option(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();

        foreach (['completed', 'cancelled'] as $status) {
            $order = $this->createOrder($customer, $status);

            $this->actingAs($admin, 'admin')
                ->get(route('admin.orders.show', $order))
                ->assertInertia(fn (Assert $page) => $page
                    ->has('statusOptions', 1)
                    ->where('statusOptions.0.value', $status)
                );
        }
    }

    public function test_order_detail_shares_localized_success_and_error_flash_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createOrder(User::factory()->create(), 'pending');

        $this->actingAs($admin, 'admin')
            ->withCookie('locale', 'en')
            ->withSession([
                'error' => 'The order status transition is invalid.',
                'success' => 'The order status was updated.',
            ])
            ->get(route('admin.orders.show', $order))
            ->assertInertia(fn (Assert $page) => $page
                ->where('flash.error', 'The order status transition is invalid.')
                ->where('flash.success', 'The order status was updated.')
            );
    }

    private function createOrder(User $customer, string $status): Order
    {
        $order = Order::create([
            'user_id' => $customer->id,
            'total_price' => 250000,
            'status' => $status,
            'note' => 'Customer delivery note',
            'admin_note' => 'Initial admin note',
        ]);
        $order->forceFill([
            'created_at' => CarbonImmutable::parse('2026-06-09 14:30:00'),
            'updated_at' => CarbonImmutable::parse('2026-06-09 14:30:00'),
        ])->saveQuietly();

        return $order;
    }

    private function createPayment(
        Order $order,
        string $status,
        string $reference,
        ?CarbonImmutable $paidAt
    ): Payment {
        return Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => $reference,
            'status' => $status,
            'amount' => 250000,
            'currency' => 'IDR',
            'payload_json' => ['private' => 'not serialized'],
            'paid_at' => $paidAt,
        ]);
    }
}
