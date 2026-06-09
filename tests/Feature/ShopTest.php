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

    public function test_shop_formats_idr_for_indonesian_locale(): void
    {
        Souvenir::factory()->create([
            'price' => 1234567,
        ]);

        $this->withHeader('Accept-Language', 'id-ID,id;q=0.9')
            ->get(route('shop.index'))
            ->assertOk()
            ->assertSee('Rp1.234.567');
    }

    public function test_shop_formats_idr_for_english_locale(): void
    {
        Souvenir::factory()->create([
            'price' => 1234567,
        ]);

        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get(route('shop.index'))
            ->assertOk()
            ->assertSee('IDR 1,234,567')
            ->assertDontSee('Rp1.234.567');
    }
}
