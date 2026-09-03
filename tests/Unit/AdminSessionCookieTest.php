<?php

namespace Tests\Unit;

use App\Http\Middleware\AdminSessionCookie;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminSessionCookieTest extends TestCase
{
    public function test_it_resolves_distinct_configured_cookie_names_for_web_and_admin_requests(): void
    {
        config([
            'session.web_cookie' => 'japan-travel-session',
            'session.admin_cookie' => 'japan-travel-admin-session',
        ]);

        $middleware = app(AdminSessionCookie::class);

        $this->assertSame(
            'japan-travel-session',
            $middleware->resolveCookieName(Request::create('/'))
        );
        $this->assertSame(
            'japan-travel-session',
            $middleware->resolveCookieName(Request::create('/administrator'))
        );
        $this->assertSame(
            'japan-travel-admin-session',
            $middleware->resolveCookieName(Request::create('/admin'))
        );
        $this->assertSame(
            'japan-travel-admin-session',
            $middleware->resolveCookieName(Request::create('/admin/login'))
        );
    }

    public function test_it_falls_back_to_distinct_app_scoped_cookie_names(): void
    {
        config([
            'app.name' => 'Japan Travel',
            'session.web_cookie' => null,
            'session.admin_cookie' => '',
        ]);

        $middleware = app(AdminSessionCookie::class);

        $this->assertSame(
            'japan-travel-session',
            $middleware->resolveCookieName(Request::create('/profile'))
        );
        $this->assertSame(
            'japan-travel-admin-session',
            $middleware->resolveCookieName(Request::create('/admin/orders'))
        );
    }
}
