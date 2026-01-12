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

        $response->assertSee('Temukan destinasi otentik, ulasan jujur, dan rekomendasi oleh-oleh yang benar-benar dibutuhkan traveler modern.');
    }

    public function test_locale_uses_accept_language_en(): void
    {
        $response = $this->get('/', [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $response->assertSee('Discover authentic destinations, honest reviews, and souvenir picks that modern travelers actually need.');
    }

    public function test_locale_toggle_cookie_overrides_header(): void
    {
        $this->get(route('lang.switch', 'en'))
            ->assertCookie('locale', 'en');

        $response = $this->withCookie('locale', 'en')
            ->get('/', [
                'HTTP_ACCEPT_LANGUAGE' => 'id-ID,id;q=0.9',
            ]);

        $response->assertSee('Discover authentic destinations, honest reviews, and souvenir picks that modern travelers actually need.');
    }
}
