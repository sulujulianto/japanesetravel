<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Auth/Login', [
            'copy' => [
                'backToUserLogin' => __('Kembali ke login pengguna'),
                'description' => __('Masuk sebagai admin untuk mengelola konten dan pesanan.'),
                'email' => __('Email'),
                'emailPlaceholder' => __('admin@email.com'),
                'eyebrow' => __('Destinasi dan oleh-oleh Jepang'),
                'footer' => __('Temukan destinasi Jepang, tulis ulasan, dan kelola pesanan oleh-oleh Anda dalam satu akun.'),
                'password' => __('Password'),
                'remember' => __('Ingat Saya'),
                'submit' => __('Masuk Admin'),
                'theme' => __('Tema'),
                'themeToggle' => __('Ganti tema'),
                'title' => __('Admin Portal'),
            ],
            'routes' => [
                'home' => route('home', absolute: false),
                'localeEn' => route('lang.switch', ['locale' => 'en'], absolute: false),
                'localeId' => route('lang.switch', ['locale' => 'id'], absolute: false),
                'submit' => route('admin.login.store', absolute: false),
                'userLogin' => route('login', absolute: false),
            ],
        ]);
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function store(AdminLoginRequest $request): SymfonyResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return Inertia::location(
            redirect()->intended(route('admin.dashboard', absolute: false))
        );
    }

    /**
     * Destroy an authenticated admin session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
