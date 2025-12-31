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
            ->assertSessionHas('cart.' . $souvenir->id, 1);

        $this->post(route('cart.update'), [
            'qty' => [$souvenir->id => 2],
        ])->assertSessionHas('cart.' . $souvenir->id, 2);

        $this->delete(route('cart.items.destroy', $souvenir->id))
            ->assertSessionMissing('cart.' . $souvenir->id);
    }
}
