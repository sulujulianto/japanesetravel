<?php

namespace Tests\Feature;

use App\Models\Souvenir;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_page_renders_successfully(): void
    {
        Souvenir::factory()->create();

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('data-shop-catalog', false)
            ->assertSee('for="shop-search"', false)
            ->assertSee('id="shop-search"', false)
            ->assertSee('for="shop-min-price"', false)
            ->assertSee('id="shop-min-price"', false)
            ->assertSee('for="shop-max-price"', false)
            ->assertSee('id="shop-max-price"', false)
            ->assertSee('for="shop-availability"', false)
            ->assertSee('id="shop-availability"', false)
            ->assertSee('for="shop-sort"', false)
            ->assertSee('id="shop-sort"', false)
            ->assertSee('data-product-grid class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"', false);
    }

    public function test_shop_uses_three_by_three_pagination_grid(): void
    {
        Cache::flush();
        Souvenir::factory()->count(10)->create();

        $firstPage = $this->get(route('shop.index'));
        $firstPage->assertOk();

        $firstPagePaginator = $firstPage->viewData('souvenirs');

        $this->assertInstanceOf(LengthAwarePaginator::class, $firstPagePaginator);
        $this->assertSame(9, $firstPagePaginator->perPage());
        $this->assertCount(9, $firstPagePaginator->items());
        $this->assertSame(9, substr_count((string) $firstPage->getContent(), 'data-product-card'));
        $firstPage->assertSee('page=2', false);

        $secondPage = $this->get(route('shop.index', ['page' => 2]));
        $secondPage->assertOk();

        $secondPagePaginator = $secondPage->viewData('souvenirs');

        $this->assertInstanceOf(LengthAwarePaginator::class, $secondPagePaginator);
        $this->assertCount(1, $secondPagePaginator->items());
        $this->assertSame(1, substr_count((string) $secondPage->getContent(), 'data-product-card'));
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
