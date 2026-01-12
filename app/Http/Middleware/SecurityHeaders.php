<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');

        $scriptSrc = ["'self'", "'unsafe-inline'", 'https://cdn.jsdelivr.net'];
        $styleSrc = ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com'];
        $imgSrc = ["'self'", 'data:'];
        $fontSrc = ["'self'", 'https://fonts.gstatic.com'];
        $connectSrc = ["'self'"];

        if (app()->environment('local')) {
            $viteHosts = [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
                'ws://localhost:5173',
                'ws://127.0.0.1:5173',
            ];

            $scriptSrc = array_merge($scriptSrc, [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
            ]);
            $styleSrc = array_merge($styleSrc, [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
            ]);
            $connectSrc = array_merge($connectSrc, $viteHosts);
        }

        $csp = [
            "default-src 'self'",
            'script-src ' . implode(' ', $scriptSrc),
            'style-src ' . implode(' ', $styleSrc),
            'img-src ' . implode(' ', $imgSrc),
            'font-src ' . implode(' ', $fontSrc),
            'connect-src ' . implode(' ', $connectSrc),
            "frame-ancestors 'self'",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
}
