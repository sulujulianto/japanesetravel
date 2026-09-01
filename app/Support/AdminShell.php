<?php

namespace App\Support;

final class AdminShell
{
    /** @return array<string, string> */
    public static function copy(): array
    {
        return [
            'closeMenu' => __('Tutup menu'),
            'dashboard' => __('Dashboard'),
            'logout' => __('Keluar'),
            'lowStock' => __('Stok Rendah'),
            'menu' => __('Menu'),
            'navigation' => __('Navigasi admin'),
            'orders' => __('Pesanan'),
            'places' => __('Destinasi'),
            'souvenirs' => __('Souvenir'),
            'theme' => __('Tema'),
            'themeDark' => __('Tema gelap'),
            'themeLight' => __('Tema terang'),
            'themeToggle' => __('Ganti tema'),
            'useDarkTheme' => __('Gunakan tema gelap'),
            'useLightTheme' => __('Gunakan tema terang'),
            'viewSite' => __('Lihat Situs'),
            'workspace' => __('Admin Workspace'),
            'workspaceDescription' => __('Pantau operasional harian'),
        ];
    }

    /** @return array<string, string> */
    public static function routes(): array
    {
        return [
            'dashboard' => route('admin.dashboard', absolute: false),
            'home' => route('home', absolute: false),
            'localeEn' => route('lang.switch', ['locale' => 'en'], absolute: false),
            'localeId' => route('lang.switch', ['locale' => 'id'], absolute: false),
            'logout' => route('admin.logout', absolute: false),
            'lowStock' => route('admin.inventory.low-stock', absolute: false),
            'orders' => route('admin.orders.index', absolute: false),
            'places' => route('admin.places.index', absolute: false),
            'souvenirs' => route('admin.souvenirs.index', absolute: false),
        ];
    }
}
