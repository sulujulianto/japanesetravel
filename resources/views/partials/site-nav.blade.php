@php
    $cartCount = collect(session('cart', []))->sum();
@endphp

<nav x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 20" class="fixed top-0 left-0 right-0 z-50 transition">
    <div :class="scrolled ? 'bg-white/90 shadow-sm dark:bg-slate-950/90' : 'bg-white/70 dark:bg-slate-950/60'" class="border-b border-slate-200/60 backdrop-blur dark:border-slate-800">
        <div class="mx-auto flex h-18 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <button class="text-xl text-slate-600 dark:text-slate-200 lg:hidden" @click="open = !open" type="button">☰</button>
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="text-2xl">⛩️</span>
                    <span class="font-display text-lg font-semibold text-slate-900 dark:text-white">Japan<span class="text-rose-500">Travel</span></span>
                </a>
            </div>

            <div class="hidden items-center gap-6 lg:flex">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">{{ __('Wisata') }}</a>
                <a href="{{ route('shop.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">{{ __('Oleh-oleh') }}</a>
                @auth
                    <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">{{ __('Pesanan Saya') }}</a>
                @endauth
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('cart.index') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white">
                    🛒
                    @if($cartCount > 0)
                        <span class="absolute -right-1 -top-1 rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-semibold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
                <div class="hidden items-center gap-2 text-xs font-semibold sm:flex">
                    <a href="{{ route('lang.switch', 'id') }}" class="rounded-full border border-slate-200 px-3 py-1 {{ App::getLocale() === 'id' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300' }}">ID</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="rounded-full border border-slate-200 px-3 py-1 {{ App::getLocale() === 'en' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300' }}">EN</a>
                </div>
                <button onclick="toggleTheme()" class="h-10 w-10 rounded-full border border-slate-200 text-lg text-slate-600 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300" title="{{ __('Ganti tema') }}" type="button">🌗</button>
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 lg:inline-flex">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white lg:inline">{{ __('Masuk') }}</a>
                    <a href="{{ route('register') }}" class="hidden rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-200 lg:inline-flex">{{ __('Daftar') }}</a>
                @endauth
            </div>
        </div>
    </div>

    <div x-cloak x-show="open" class="bg-white/95 px-4 pb-6 pt-4 text-sm font-semibold text-slate-700 shadow-sm dark:bg-slate-950/95 dark:text-slate-200 lg:hidden">
        <div class="space-y-3">
            <a href="{{ route('home') }}" class="block">{{ __('Wisata') }}</a>
            <a href="{{ route('shop.index') }}" class="block">{{ __('Oleh-oleh') }}</a>
            @auth
                <a href="{{ route('orders.index') }}" class="block">{{ __('Pesanan Saya') }}</a>
                <a href="{{ route('dashboard') }}" class="block">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="block">{{ __('Masuk') }}</a>
                <a href="{{ route('register') }}" class="block">{{ __('Daftar') }}</a>
            @endauth
        </div>
        <div class="mt-4 flex items-center gap-2 text-xs font-semibold">
            <a href="{{ route('lang.switch', 'id') }}" class="rounded-full border border-slate-200 px-3 py-1 {{ App::getLocale() === 'id' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">ID</a>
            <a href="{{ route('lang.switch', 'en') }}" class="rounded-full border border-slate-200 px-3 py-1 {{ App::getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">EN</a>
        </div>
    </div>
</nav>
