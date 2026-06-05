@php
    $currentUser = Auth::user();
    $cartCount = collect(session('cart', []))->sum();
    $desktopLink = 'rounded-full px-3 py-2 text-sm font-semibold transition';
    $desktopLinkIdle = 'text-[#526071] hover:bg-[#F1EEE8] hover:text-[#1F2937] dark:text-[#AEB8C7] dark:hover:bg-[#1F2630] dark:hover:text-[#F4F1ED]';
    $desktopLinkActive = 'bg-[#F1EEE8] text-[#1F2937] dark:bg-[#1F2630] dark:text-[#F4F1ED]';
    $desktopControl = 'inline-flex h-9 items-center rounded-full border border-[#E7E3DC] px-3 text-xs font-semibold text-[#3F3F3F] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]';
    $mobileLink = 'block rounded-lg px-3 py-2.5 text-sm font-semibold text-[#374151] transition hover:bg-white hover:text-[#8F2E2E] dark:text-[#D8DEE8] dark:hover:bg-[#0E1116] dark:hover:text-[#D96B6B]';
@endphp

<nav class="site-navbar sticky top-0 z-50 border-b border-[#E7E3DC] bg-[#FAF8F3] text-[#222222] shadow-[0_1px_0_rgba(34,34,34,0.02)] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-slate-100 dark:shadow-none">
    <div class="hidden lg:block">
        <div class="mx-auto grid min-h-16 max-w-[90rem] grid-cols-[1fr_auto_1fr] items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="inline-flex w-fit items-center gap-2.5 text-[#222222] transition hover:text-[#8F2E2E] dark:text-slate-100 dark:hover:text-[#D96B6B]">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#DDD6CC] bg-white text-sm font-semibold tracking-tight text-[#A6423A] dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#D96B6B]">JT</span>
                <span class="text-base font-semibold tracking-tight">Japan<span class="text-[#A6423A] dark:text-[#D96B6B]">Travel</span></span>
            </a>

            <div class="flex items-center gap-1">
                <a href="{{ route('dashboard') }}" class="{{ $desktopLink }} {{ request()->routeIs('dashboard') ? $desktopLinkActive : $desktopLinkIdle }}">{{ __('Dashboard') }}</a>
                <a href="{{ route('orders.index') }}" class="{{ $desktopLink }} {{ request()->routeIs('orders.*') ? $desktopLinkActive : $desktopLinkIdle }}">{{ __('Pesanan Saya') }}</a>
                <a href="{{ route('shop.index') }}" class="{{ $desktopLink }} {{ $desktopLinkIdle }}">{{ __('Oleh-oleh') }}</a>
                <a href="{{ route('profile.edit') }}" class="{{ $desktopLink }} {{ request()->routeIs('profile.*') ? $desktopLinkActive : $desktopLinkIdle }}">{{ __('Profil') }}</a>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('cart.index') }}" class="{{ $desktopControl }}">
                    <span>{{ __('Keranjang') }}</span>
                    @if($cartCount > 0)
                        <span class="ml-2 rounded-full bg-[#B33A3A] px-2 py-0.5 text-[10px] font-semibold text-white dark:bg-[#D96B6B] dark:text-[#0E1116]">{{ $cartCount }}</span>
                    @endif
                </a>

                <div class="inline-flex items-center rounded-full border border-[#E7E3DC] bg-white p-1 text-xs font-semibold dark:border-[#2A333D] dark:bg-[#0E1116]">
                    <a href="{{ route('lang.switch', 'id') }}" class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'id' ? 'bg-[#222222] text-white dark:bg-slate-100 dark:text-[#0E1116]' : 'text-[#525252] hover:text-[#8F2E2E] dark:text-slate-300 dark:hover:text-[#D96B6B]' }}">ID</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'en' ? 'bg-[#222222] text-white dark:bg-slate-100 dark:text-[#0E1116]' : 'text-[#525252] hover:text-[#8F2E2E] dark:text-slate-300 dark:hover:text-[#D96B6B]' }}">EN</a>
                </div>

                <button onclick="toggleTheme()" class="{{ $desktopControl }}" title="{{ __('Ganti tema') }}" type="button">
                    {{ __('Tema') }}
                </button>

                <details class="account-menu relative">
                    <summary class="flex h-9 cursor-pointer items-center gap-1.5 px-2 text-xs font-semibold text-[#526071] transition hover:text-[#8F2E2E] dark:text-[#AEB8C7] dark:hover:text-[#D96B6B]">
                        <span class="max-w-24 truncate">{{ $currentUser?->username }}</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 8 4 4 4-4" />
                        </svg>
                    </summary>
                    <div class="absolute right-0 top-full mt-2 w-48 rounded-xl border border-[#E7E3DC] bg-white p-2 shadow-lg dark:border-[#2A333D] dark:bg-[#161B22]">
                        <p class="truncate px-3 py-2 text-xs text-[#526071] dark:text-[#AEB8C7]">{{ $currentUser?->email }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-[#E7E3DC] pt-1 dark:border-[#2A333D]">
                            @csrf
                            <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-[#9F2A2A] hover:bg-red-50 dark:text-[#F0A0A0] dark:hover:bg-red-950/30">{{ __('Keluar') }}</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <details class="account-menu lg:hidden">
        <summary class="block cursor-pointer select-none">
            <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-2 px-3 sm:gap-3 sm:px-6">
                <span class="inline-flex min-w-0 items-center gap-2 text-[#222222] dark:text-slate-100 sm:gap-2.5">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[#DDD6CC] bg-white text-xs font-semibold tracking-tight text-[#A6423A] dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#D96B6B] sm:h-9 sm:w-9 sm:text-sm">JT</span>
                    <span class="truncate text-sm font-semibold tracking-tight sm:text-base">Japan<span class="text-[#A6423A] dark:text-[#D96B6B]">Travel</span></span>
                </span>

                <span class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                    <a href="{{ route('cart.index') }}" onclick="event.stopPropagation();" class="relative inline-flex h-9 items-center rounded-full border border-[#E7E3DC] px-2.5 text-xs font-semibold text-[#3F3F3F] dark:border-[#2A333D] dark:text-slate-300 sm:px-3">
                        {{ __('Keranjang') }}
                        @if($cartCount > 0)
                            <span class="ml-1.5 rounded-full bg-[#B33A3A] px-1.5 py-0.5 text-[9px] font-semibold text-white dark:bg-[#D96B6B] dark:text-[#0E1116]">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <span class="inline-flex h-9 items-center rounded-full border border-[#E7E3DC] px-3 text-xs font-semibold text-[#3F3F3F] dark:border-[#2A333D] dark:text-slate-300">{{ __('Menu') }}</span>
                </span>
            </div>
        </summary>

        <div class="border-t border-[#E7E3DC] bg-[#FAF8F3] dark:border-[#2A333D] dark:bg-[#161B22]">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
                <div class="border-b border-[#E7E3DC] px-3 pb-3 dark:border-[#2A333D]">
                    <p class="truncate text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $currentUser?->username }}</p>
                    <p class="mt-1 truncate text-xs text-[#526071] dark:text-[#AEB8C7]">{{ $currentUser?->email }}</p>
                </div>

                <div class="mt-2 space-y-1">
                    <a href="{{ route('dashboard') }}" class="{{ $mobileLink }}">{{ __('Dashboard') }}</a>
                    <a href="{{ route('orders.index') }}" class="{{ $mobileLink }}">{{ __('Pesanan Saya') }}</a>
                    <a href="{{ route('shop.index') }}" class="{{ $mobileLink }}">{{ __('Oleh-oleh') }}</a>
                    <a href="{{ route('profile.edit') }}" class="{{ $mobileLink }}">{{ __('Profil') }}</a>
                    <button onclick="toggleTheme()" type="button" class="{{ $mobileLink }} w-full text-left">{{ __('Tema') }}</button>
                </div>

                <div class="mt-3 flex items-center gap-2 border-t border-[#E7E3DC] px-3 pt-3 text-xs font-semibold dark:border-[#2A333D]">
                    <a href="{{ route('lang.switch', 'id') }}" class="rounded-full border border-[#E7E3DC] px-3 py-1.5 {{ App::getLocale() === 'id' ? 'bg-[#222222] text-white dark:bg-slate-100 dark:text-[#0E1116]' : 'text-[#525252] dark:border-[#2A333D] dark:text-slate-300' }}">ID</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="rounded-full border border-[#E7E3DC] px-3 py-1.5 {{ App::getLocale() === 'en' ? 'bg-[#222222] text-white dark:bg-slate-100 dark:text-[#0E1116]' : 'text-[#525252] dark:border-[#2A333D] dark:text-slate-300' }}">EN</a>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-3 border-t border-[#E7E3DC] px-3 pt-3 dark:border-[#2A333D]">
                    @csrf
                    <button type="submit" class="block w-full rounded-lg py-2.5 text-left text-sm font-semibold text-[#9F2A2A] hover:text-[#7A1F1F] dark:text-[#F0A0A0]">{{ __('Keluar') }}</button>
                </form>
            </div>
        </div>
    </details>
</nav>
