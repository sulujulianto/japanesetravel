<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderItemImageCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_history_uses_snapshot_image_url_when_current_product_image_is_missing(): void
    {
        Storage::fake('public');
        config(['media.disk' => 'public']);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $souvenir = Souvenir::factory()->create([
            'image' => null,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 120000,
            'status' => 'pending',
            'note' => 'Pesanan uji',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'souvenir_id' => $souvenir->id,
            'quantity' => 1,
            'price' => 120000,
            'product_name' => 'Snapshot Tea',
            'product_price' => 120000,
            'product_image' => 'legacy/orders/snapshot-tea.jpg',
        ]);

        $expectedUrl = Storage::disk('public')->url('legacy/orders/snapshot-tea.jpg');

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($expectedUrl, false);
    }
}
