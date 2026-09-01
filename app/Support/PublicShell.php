<?php

namespace App\Support;

final class PublicShell
{
    /** @return array<string, string> */
    public static function copy(): array
    {
        return [
            'brand' => Brand::name(),
            'cart' => __('Keranjang'),
            'closeMenu' => __('Tutup menu'),
            'contact' => __('Kontak'),
            'contactDescription' => __('Kontak perjalanan dapat dikonfirmasi melalui kanal resmi yang tersedia pada halaman destinasi.'),
            'dashboard' => __('Dashboard'),
            'destinations' => __('Wisata'),
            'footerDescription' => __('Platform untuk menemukan destinasi :region, membaca ulasan, dan berbelanja oleh-oleh pilihan.', [
                'region' => Brand::region(),
            ]),
            'footerProject' => Brand::legalName().'.',
            'footerTechnology' => __('Dibuat dengan Laravel, Vue, dan Inertia.'),
            'login' => __('Masuk'),
            'menu' => __('Buka menu'),
            'navigation' => __('Navigasi utama'),
            'orders' => __('Pesanan Saya'),
            'register' => __('Daftar'),
            'souvenirs' => __('Oleh-oleh'),
            'theme' => __('Tema'),
            'themeDark' => __('Tema gelap'),
            'themeLight' => __('Tema terang'),
            'themeToggle' => __('Ganti tema'),
            'useDarkTheme' => __('Gunakan tema gelap'),
            'useLightTheme' => __('Gunakan tema terang'),
        ];
    }

    /** @return array<string, string> */
    public static function routes(): array
    {
        return [
            'cart' => route('cart.index', absolute: false),
            'dashboard' => route('dashboard', absolute: false),
            'home' => route('home', absolute: false),
            'localeEn' => route('lang.switch', ['locale' => 'en'], absolute: false),
            'localeId' => route('lang.switch', ['locale' => 'id'], absolute: false),
            'login' => route('login', absolute: false),
            'orders' => route('orders.index', absolute: false),
            'places' => route('places.index', absolute: false),
            'register' => route('register', absolute: false),
            'shop' => route('shop.index', absolute: false),
        ];
    }
}
