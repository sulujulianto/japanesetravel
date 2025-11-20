<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah ada data bahasa di Session
        if (Session::has('locale')) {
            // Jika ada, set bahasa aplikasi sesuai session
            App::setLocale(Session::get('locale'));
        }

        return $next($request);
    }
}