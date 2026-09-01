<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_account_pages_keep_the_public_navigation_and_footer(): void
    {
        foreach ([route('login'), route('register'), route('password.request')] as $url) {
            $response = $this->get($url);

            $response
                ->assertOk()
                ->assertSee('data-public-navigation', false)
                ->assertSee('data-public-footer', false)
                ->assertSee('data-theme-toggle', false)
                ->assertSee('href="'.route('home').'"', false)
                ->assertSee('href="'.route('places.index').'"', false)
                ->assertSee('href="'.route('shop.index').'"', false)
                ->assertSee('href="'.route('cart.index').'"', false);

            $this->assertDoesNotMatchRegularExpression(
                '/<summary\b[^>]*>(?:(?!<\/summary>)[\s\S])*?<(?:a|button|input|select|textarea)\b/i',
                (string) $response->getContent(),
            );
        }
    }

    public function test_authenticated_account_pages_keep_the_public_navigation_and_footer(): void
    {
        $user = User::factory()->create();

        foreach ([route('dashboard'), route('orders.index'), route('profile.edit')] as $url) {
            $response = $this->actingAs($user)->get($url);

            $response
                ->assertOk()
                ->assertSee('data-public-navigation', false)
                ->assertSee('data-public-footer', false)
                ->assertSee('data-theme-toggle', false)
                ->assertSee('href="'.route('home').'"', false)
                ->assertSee('href="'.route('places.index').'"', false)
                ->assertSee('href="'.route('shop.index').'"', false)
                ->assertSee('href="'.route('profile.edit').'"', false)
                ->assertSee('action="'.route('logout').'"', false);
        }
    }

    public function test_admin_login_remains_a_separate_application_shell(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee('data-public-navigation', false)
            ->assertDontSee('data-public-footer', false);
    }

    public function test_central_brand_configuration_reaches_blade_and_inertia_shells(): void
    {
        config()->set('brand', [
            'name' => 'Europe Travel',
            'mark' => 'ET',
            'legal_name' => 'Europe Travel Studio',
            'region' => ['id' => 'Eropa', 'en' => 'Europe'],
        ]);

        $this->withCookie('locale', 'id')
            ->get(route('places.index'))
            ->assertOk()
            ->assertSee('Europe Travel')
            ->assertSee('>ET</span>', false);

        $this->withCookie('locale', 'id')
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('app.name', 'Europe Travel')
                ->where('app.mark', 'ET')
                ->where('app.region', 'Eropa')
                ->where('copy.heroTitle', 'Temukan destinasi Eropa dan oleh-oleh pilihan.')
            );
    }
}
