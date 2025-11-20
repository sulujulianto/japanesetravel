<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // CEK 1: Apakah pengguna sudah login?
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // CEK 2: Apakah role pengguna adalah 'admin'?
        // Kita ambil data role dari database user yang sedang login
        if (Auth::user()->role !== 'admin') {
            // Jika bukan admin, lempar keluar (Forbidden 403)
            abort(403, 'AKSES DITOLAK: Anda bukan Administrator!');
        }

        // Jika lolos kedua cek di atas, silakan lanjut
        return $next($request);
    }
}