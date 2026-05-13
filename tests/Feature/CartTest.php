<?php

namespace Tests\Feature;

use App\Models\Souvenir;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_add_update_remove_flow(): void
    {
        $souvenir = Souvenir::factory()->create([
            'stock' => 10,
        ]);

        $this->post(route('cart.add', $souvenir->id))
            ->assertSessionHas('cart.'.$souvenir->id, 1);

        $this->post(route('cart.update'), [
            'qty' => [$souvenir->id => 2],
        ])->assertSessionHas('cart.'.$souvenir->id, 2);

        $this->delete(route('cart.items.destroy', $souvenir->id))
            ->assertSessionMissing('cart.'.$souvenir->id);
    }

    public function test_add_cart_with_non_existing_souvenir_is_rejected(): void
    {
        $this->post(route('cart.add', 999999))
            ->assertSessionHas('error')
            ->assertSessionMissing('cart.999999');
    }

    public function test_add_cart_with_zero_stock_is_rejected(): void
    {
        $souvenir = Souvenir::factory()->create([
            'stock' => 0,
        ]);

        $this->post(route('cart.add', $souvenir->id))
            ->assertSessionHas('error')
            ->assertSessionMissing('cart.'.$souvenir->id);
    }

    public function test_add_cart_does_not_exceed_stock_for_existing_item(): void
    {
        $souvenir = Souvenir::factory()->create([
            'stock' => 2,
        ]);

        $this->post(route('cart.add', $souvenir->id))
            ->assertSessionHas('cart.'.$souvenir->id, 1);
        $this->post(route('cart.add', $souvenir->id))
            ->assertSessionHas('cart.'.$souvenir->id, 2);
        $this->post(route('cart.add', $souvenir->id))
            ->assertSessionHas('cart.'.$souvenir->id, 2)
            ->assertSessionHas('error');
    }

    public function test_update_cart_quantity_above_stock_is_clamped(): void
    {
        $souvenir = Souvenir::factory()->create([
            'stock' => 3,
        ]);

        $this->post(route('cart.add', $souvenir->id))
            ->assertSessionHas('cart.'.$souvenir->id, 1);

        $this->post(route('cart.update'), [
            'qty' => [$souvenir->id => 99],
        ])
            ->assertSessionHas('cart.'.$souvenir->id, 3)
            ->assertSessionHas('error');
    }

    public function test_update_cart_removes_item_when_souvenir_is_deleted(): void
    {
        $souvenir = Souvenir::factory()->create([
            'stock' => 5,
        ]);

        $this->post(route('cart.add', $souvenir->id))
            ->assertSessionHas('cart.'.$souvenir->id, 1);

        $souvenir->delete();

        $this->post(route('cart.update'), [
            'qty' => [$souvenir->id => 2],
        ])
            ->assertSessionMissing('cart.'.$souvenir->id)
            ->assertSessionHas('error');
    }
}
