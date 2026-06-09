<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_uses_accept_language_id(): void
    {
        $response = $this->get('/', [
            'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
        ]);

        $response->assertSee('Temukan destinasi Jepang dan oleh-oleh pilihan.');
    }

    public function test_locale_uses_accept_language_en(): void
    {
        $response = $this->get('/', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response->assertSee('Discover Japan destinations and curated souvenirs.');
    }

    public function test_locale_toggle_cookie_overrides_header(): void
    {
        $this->get(route('lang.switch', 'en'))
            ->assertCookie('locale', 'en');

        $response = $this->withCookie('locale', 'en')
            ->get('/', [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ]);

        $response->assertSee('Discover Japan destinations and curated souvenirs.');
    }

    public function test_places_catalog_uses_english_copy(): void
    {
        $response = $this->get('/places', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response
            ->assertOk()
            ->assertSee('Destination catalog')
            ->assertDontSee('Katalog destinasi');
    }

    public function test_shop_uses_english_copy(): void
    {
        $response = $this->get('/shop', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response
            ->assertOk()
            ->assertSee('Curated souvenir catalog')
            ->assertDontSee('Katalog oleh-oleh pilihan');
    }

    public function test_empty_cart_uses_english_copy(): void
    {
        $response = $this->get('/cart', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response
            ->assertOk()
            ->assertSee('Souvenir cart')
            ->assertSee('Your cart has no souvenirs yet')
            ->assertDontSee('Keranjang oleh-oleh');
    }
}
