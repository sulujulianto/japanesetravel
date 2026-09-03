<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        $cookie = $this->resolveCookieName($request);

        config(['session.cookie' => $cookie]);

        $session = app('session');

        if ($session->getName() !== $cookie) {
            $session->flush();
        }

        $session->setName($cookie);

        return $next($request);
    }

    public function resolveCookieName(Request $request): string
    {
        $isAdminRequest = $request->is('admin') || $request->is('admin/*');
        $configKey = $isAdminRequest ? 'session.admin_cookie' : 'session.web_cookie';
        $configuredCookie = config($configKey);

        if (is_string($configuredCookie) && $configuredCookie !== '') {
            return $configuredCookie;
        }

        $suffix = $isAdminRequest ? '-admin-session' : '-session';

        return Str::slug((string) config('app.name', 'laravel')).$suffix;
    }
}
