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

        $adminCookie = config('session.admin_cookie');
        if (! $adminCookie) {
            $adminCookie = Str::slug((string) config('app.name', 'laravel')) . '-admin-session';
        }

        $webCookie = config('session.web_cookie');
        if (! $webCookie) {
            $webCookie = Str::slug((string) config('app.name', 'laravel')) . '-session';
        }

        $cookie = ($request->is('admin') || $request->is('admin/*'))
            ? $adminCookie
            : $webCookie;

        config(['session.cookie' => $cookie]);

        $session = app('session');

        if ($session->getName() !== $cookie) {
            $session->flush();
        }

        $session->setName($cookie);

        return $next($request);
    }
}
