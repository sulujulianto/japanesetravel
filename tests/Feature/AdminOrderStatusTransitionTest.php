<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_pending_order_to_processing(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrderWithStatus('pending');

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'processing',
                'admin_note' => 'Pembayaran diverifikasi admin.',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'admin_note' => 'Pembayaran diverifikasi admin.',
        ]);
    }

    public function test_admin_can_update_pending_order_to_cancelled(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrderWithStatus('pending');

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'cancelled',
                'admin_note' => 'Pesanan dibatalkan oleh admin.',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'admin_note' => 'Pesanan dibatalkan oleh admin.',
        ]);
    }

    public function test_admin_cannot_update_pending_order_directly_to_completed(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrderWithStatus('pending', 'Catatan awal');

        $response = $this->actingAs($admin, 'admin')
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->put(route('admin.orders.update', $order), [
                'status' => 'completed',
                'admin_note' => 'Seharusnya tidak tersimpan.',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('error', 'The order status transition is invalid.');

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertSame('Catatan awal', $order->admin_note);
    }

    public function test_admin_can_update_processing_order_to_completed(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrderWithStatus('processing');

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'completed',
                'admin_note' => 'Pesanan selesai dikirim.',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
            'admin_note' => 'Pesanan selesai dikirim.',
        ]);
    }

    public function test_admin_can_update_processing_order_to_cancelled(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrderWithStatus('processing');

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'cancelled',
                'admin_note' => 'Pembatalan manual oleh admin.',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'admin_note' => 'Pembatalan manual oleh admin.',
        ]);
    }

    public function test_admin_cannot_downgrade_completed_order_to_other_statuses(): void
    {
        $admin = $this->createAdmin();

        foreach (['pending', 'processing', 'cancelled'] as $targetStatus) {
            $order = $this->createOrderWithStatus('completed', 'Completed note');

            $response = $this->actingAs($admin, 'admin')
                ->put(route('admin.orders.update', $order), [
                    'status' => $targetStatus,
                    'admin_note' => 'Tidak boleh berubah.',
                ]);

            $response->assertRedirect(route('admin.orders.show', $order));
            $response->assertSessionHas('error');

            $order->refresh();
            $this->assertSame('completed', $order->status);
            $this->assertSame('Completed note', $order->admin_note);
        }
    }

    public function test_admin_cannot_revive_cancelled_order_to_other_statuses(): void
    {
        $admin = $this->createAdmin();

        foreach (['pending', 'processing', 'completed'] as $targetStatus) {
            $order = $this->createOrderWithStatus('cancelled', 'Cancelled note');

            $response = $this->actingAs($admin, 'admin')
                ->put(route('admin.orders.update', $order), [
                    'status' => $targetStatus,
                    'admin_note' => 'Tidak boleh berubah.',
                ]);

            $response->assertRedirect(route('admin.orders.show', $order));
            $response->assertSessionHas('error');

            $order->refresh();
            $this->assertSame('cancelled', $order->status);
            $this->assertSame('Cancelled note', $order->admin_note);
        }
    }

    public function test_regular_user_cannot_access_admin_order_update(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $order = $this->createOrderWithStatus('pending');

        $response = $this->actingAs($user, 'web')
            ->put(route('admin.orders.update', $order), [
                'status' => 'processing',
                'admin_note' => 'Tidak boleh diproses.',
            ]);

        $response->assertRedirect(route('admin.login'));

        $order->refresh();
        $this->assertSame('pending', $order->status);
    }

    public function test_admin_cancellation_restores_reserved_stock_exactly_once(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrderWithStatus('processing');
        $souvenir = Souvenir::factory()->create(['stock' => 3]);
        OrderItem::create([
            'order_id' => $order->id,
            'souvenir_id' => $souvenir->id,
            'quantity' => 2,
            'price' => 100000,
            'product_name' => 'Test souvenir',
            'product_price' => 100000,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'cancelled',
                'admin_note' => 'Cancellation with inventory restoration.',
            ])
            ->assertSessionHas('success');

        $this->assertSame(5, $souvenir->fresh()->stock);
        $this->assertNotNull($order->fresh()->stock_restored_at);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'cancelled',
                'admin_note' => 'Repeated cancellation.',
            ])
            ->assertSessionHas('success');

        $this->assertSame(5, $souvenir->fresh()->stock);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function createOrderWithStatus(string $status, ?string $adminNote = null): Order
    {
        $customer = User::factory()->create([
            'role' => 'user',
        ]);

        return Order::create([
            'user_id' => $customer->id,
            'total_price' => 120000,
            'status' => $status,
            'note' => 'Test order',
            'admin_note' => $adminNote,
        ]);
    }
}
