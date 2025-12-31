<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale');

        if (! $locale) {
            $acceptLanguage = strtolower((string) $request->header('Accept-Language', ''));
            $locale = str_contains($acceptLanguage, 'id') ? 'id' : 'en';
        }

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = config('app.fallback_locale', config('app.locale'));
        }

        App::setLocale($locale);

        return $next($request);
    }
}
