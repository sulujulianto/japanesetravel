@php
    $cartCount = collect(session('cart', []))->sum();
    $navLink = 'text-sm font-medium text-[#3F3F3F] transition hover:text-[#8F2E2E] dark:text-slate-300 dark:hover:text-[#D96B6B]';
    $control = 'inline-flex h-9 items-center rounded-full border border-[#E7E3DC] px-3 text-xs font-semibold text-[#3F3F3F] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:text-slate-300 dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]';
    $mobileLink = 'block rounded-lg px-3 py-2 text-sm font-medium text-[#3F3F3F] transition hover:bg-white hover:text-[#8F2E2E] dark:text-slate-300 dark:hover:bg-[#0E1116] dark:hover:text-[#D96B6B]';
@endphp

<nav class="site-navbar sticky top-0 z-50 border-b border-[#E7E3DC] bg-[#FAF8F3] text-[#222222] shadow-[0_1px_0_rgba(34,34,34,0.02)] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-slate-100 dark:shadow-none">
    <div class="hidden lg:block">
        <div class="mx-auto grid min-h-16 max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 text-[#222222] transition hover:text-[#8F2E2E] dark:text-slate-100 dark:hover:text-[#D96B6B]">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#DDD6CC] bg-white text-sm font-semibold tracking-tight text-[#A6423A] dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#D96B6B]">
                    JT
                </span>
                <span class="text-base font-semibold tracking-tight">
                    Japan<span class="text-[#A6423A] dark:text-[#D96B6B]">Travel</span>
                </span>
            </a>

            <div class="flex items-center gap-7">
                <a href="{{ route('places.index') }}" class="{{ $navLink }}">{{ __('Wisata') }}</a>
                <a href="{{ route('shop.index') }}" class="{{ $navLink }}">{{ __('Oleh-oleh') }}</a>
                @auth
                    <a href="{{ route('orders.index') }}" class="{{ $navLink }}">{{ __('Pesanan Saya') }}</a>
                @endauth
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('cart.index') }}" class="{{ $control }}">
                    <span>{{ __('Keranjang') }}</span>
                    @if($cartCount > 0)
                        <span class="ml-2 rounded-full bg-[#B33A3A] px-2 py-0.5 text-[10px] font-semibold text-white dark:bg-[#D96B6B] dark:text-[#0E1116]">{{ $cartCount }}</span>
                    @endif
                </a>

                <div class="inline-flex items-center rounded-full border border-[#E7E3DC] bg-white p-1 text-xs font-semibold dark:border-[#2A333D] dark:bg-[#0E1116]">
                    <a href="{{ route('lang.switch', 'id') }}" class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'id' ? 'bg-[#222222] text-white dark:bg-slate-100 dark:text-[#0E1116]' : 'text-[#525252] hover:text-[#8F2E2E] dark:text-slate-300 dark:hover:text-[#D96B6B]' }}">ID</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'en' ? 'bg-[#222222] text-white dark:bg-slate-100 dark:text-[#0E1116]' : 'text-[#525252] hover:text-[#8F2E2E] dark:text-slate-300 dark:hover:text-[#D96B6B]' }}">EN</a>
                </div>

                <button onclick="toggleTheme()" class="{{ $control }}" title="{{ __('Ganti tema') }}" type="button">{{ __('Tema') }}</button>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex h-9 items-center rounded-full bg-[#B33A3A] px-4 text-xs font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="{{ $navLink }}">{{ __('Masuk') }}</a>
                    <a href="{{ route('register') }}" class="inline-flex h-9 items-center rounded-full bg-[#B33A3A] px-4 text-xs font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">{{ __('Daftar') }}</a>
                @endauth
            </div>
        </div>
    </div>

    <details class="site-mobile-menu lg:hidden">
        <summary class="block cursor-pointer select-none">
            <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6">
                <span class="inline-flex items-center gap-2.5 text-[#222222] dark:text-slate-100">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#DDD6CC] bg-white text-sm font-semibold tracking-tight text-[#A6423A] dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#D96B6B]">
                        JT
                    </span>
                    <span class="text-base font-semibold tracking-tight">
                        Japan<span class="text-[#A6423A] dark:text-[#D96B6B]">Travel</span>
                    </span>
                </span>
                <span class="flex shrink-0 items-center gap-2">
                    <button onclick="event.stopPropagation(); toggleTheme();" class="inline-flex h-9 items-center rounded-full border border-[#E7E3DC] px-3 text-xs font-semibold text-[#3F3F3F] dark:border-[#2A333D] dark:text-slate-300" title="{{ __('Ganti tema') }}" type="button">{{ __('Tema') }}</button>
                    <span class="inline-flex h-9 items-center rounded-full border border-[#E7E3DC] px-3 text-xs font-semibold text-[#3F3F3F] dark:border-[#2A333D] dark:text-slate-300">
                        {{ __('Menu') }}
                    </span>
                </span>
            </div>
        </summary>

        <div class="border-t border-[#E7E3DC] bg-[#FAF8F3] dark:border-[#2A333D] dark:bg-[#161B22]">
            <div class="mx-auto max-w-7xl space-y-2 px-4 py-4 sm:px-6">
                <a href="{{ route('places.index') }}" class="{{ $mobileLink }}">{{ __('Wisata') }}</a>
                <a href="{{ route('shop.index') }}" class="{{ $mobileLink }}">{{ __('Oleh-oleh') }}</a>
                <a href="{{ route('cart.index') }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-[#3F3F3F] transition hover:bg-white hover:text-[#8F2E2E] dark:text-slate-300 dark:hover:bg-[#0E1116] dark:hover:text-[#D96B6B]">
                    <span>{{ __('Keranjang') }}</span>
                    @if($cartCount > 0)
                        <span class="rounded-full bg-[#B33A3A] px-2 py-0.5 text-[10px] font-semibold text-white dark:bg-[#D96B6B] dark:text-[#0E1116]">{{ $cartCount }}</span>
                    @endif
                </a>
                @auth
                    <a href="{{ route('orders.index') }}" class="{{ $mobileLink }}">{{ __('Pesanan Saya') }}</a>
                    <a href="{{ route('dashboard') }}" class="{{ $mobileLink }}">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="{{ $mobileLink }}">{{ __('Masuk') }}</a>
                    <a href="{{ route('register') }}" class="{{ $mobileLink }}">{{ __('Daftar') }}</a>
                @endauth
                <div class="flex items-center gap-2 px-3 pt-2 text-xs font-semibold">
                    <a href="{{ route('lang.switch', 'id') }}" class="rounded-full border border-[#E7E3DC] px-3 py-1 {{ App::getLocale() === 'id' ? 'bg-[#222222] text-white dark:bg-slate-100 dark:text-[#0E1116]' : 'text-[#525252] dark:border-[#2A333D] dark:text-slate-300' }}">ID</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="rounded-full border border-[#E7E3DC] px-3 py-1 {{ App::getLocale() === 'en' ? 'bg-[#222222] text-white dark:bg-slate-100 dark:text-[#0E1116]' : 'text-[#525252] dark:border-[#2A333D] dark:text-slate-300' }}">EN</a>
                </div>
            </div>
        </div>
    </details>
</nav>
