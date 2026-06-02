<?php

namespace Tests\Feature;

use App\Models\Souvenir;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_page_renders_successfully(): void
    {
        Souvenir::factory()->create();

        $this->get(route('shop.index'))
            ->assertOk();
    }
}
