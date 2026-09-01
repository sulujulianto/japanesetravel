@php
    $currentUser = Auth::user();
    $cartCount = collect(session('cart', []))->sum();
    $brand = \App\Support\Brand::props();
    $copy = \App\Support\PublicShell::copy();
    $navLink = 'rounded-lg px-3 py-2 text-sm font-semibold text-[var(--public-muted)] transition hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-ink)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--public-accent)]';
    $navLinkActive = 'bg-[var(--public-surface-muted)] text-[var(--public-ink)]';
    $control = 'inline-flex min-h-10 items-center rounded-full border border-[var(--public-border)] px-3 text-xs font-semibold text-[var(--public-ink)] transition hover:border-[var(--public-accent)] hover:text-[var(--public-accent)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--public-accent)]';
    $mobileLink = 'block rounded-lg px-3 py-2.5 text-sm font-semibold text-[var(--public-ink)] transition hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]';
@endphp

<nav data-public-navigation aria-label="{{ $copy['navigation'] }}" class="public-navigation site-navbar sticky top-0 z-50 border-b">
    <div class="hidden lg:block">
        <div class="mx-auto grid min-h-16 max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-5 px-6 lg:px-8">
            <a href="{{ route('home') }}" class="inline-flex w-fit items-center gap-2.5 text-[var(--public-ink)] transition hover:text-[var(--public-accent)]">
                <span class="brand-mark" aria-hidden="true">{{ $brand['mark'] }}</span>
                <span class="text-base font-semibold tracking-tight">{{ $brand['name'] }}</span>
            </a>

            <div class="flex items-center gap-1">
                <a href="{{ route('places.index') }}" @if(request()->routeIs('places.*', 'place.show')) aria-current="page" @endif class="{{ $navLink }} {{ request()->routeIs('places.*', 'place.show') ? $navLinkActive : '' }}">{{ $copy['destinations'] }}</a>
                <a href="{{ route('shop.index') }}" @if(request()->routeIs('shop.*')) aria-current="page" @endif class="{{ $navLink }} {{ request()->routeIs('shop.*') ? $navLinkActive : '' }}">{{ $copy['souvenirs'] }}</a>
                @auth
                    <a href="{{ route('orders.index') }}" @if(request()->routeIs('orders.*')) aria-current="page" @endif class="{{ $navLink }} {{ request()->routeIs('orders.*') ? $navLinkActive : '' }}">{{ $copy['orders'] }}</a>
                @endauth
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('cart.index') }}" class="{{ $control }}">
                    <span>{{ $copy['cart'] }}</span>
                    @if($cartCount > 0)
                        <span class="ml-2 rounded-full bg-[var(--public-accent)] px-2 py-0.5 text-[10px] font-semibold text-white">{{ $cartCount }}</span>
                    @endif
                </a>

                <div class="inline-flex items-center rounded-full border border-[var(--public-border)] bg-[var(--public-surface)] p-1 text-xs font-semibold">
                    <a href="{{ route('lang.switch', 'id') }}" aria-label="Bahasa Indonesia" @if(App::getLocale() === 'id') aria-current="page" @endif class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'id' ? 'bg-[var(--public-ink)] text-[var(--public-surface)]' : 'text-[var(--public-muted)] hover:text-[var(--public-accent)]' }}">ID</a>
                    <a href="{{ route('lang.switch', 'en') }}" aria-label="English" @if(App::getLocale() === 'en') aria-current="page" @endif class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'en' ? 'bg-[var(--public-ink)] text-[var(--public-surface)]' : 'text-[var(--public-muted)] hover:text-[var(--public-accent)]' }}">EN</a>
                </div>

                <x-theme-toggle />

                @auth
                    <details class="account-menu relative">
                        <summary class="flex min-h-10 cursor-pointer items-center gap-2 rounded-full bg-[var(--public-accent)] px-4 text-xs font-semibold text-white transition hover:bg-[var(--public-accent-active)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--public-accent)] focus-visible:ring-offset-2">
                            <span class="max-w-28 truncate">{{ $currentUser?->username }}</span>
                            <span aria-hidden="true">⌄</span>
                        </summary>
                        <div class="absolute right-0 top-full mt-2 w-56 rounded-xl border border-[var(--public-border)] bg-[var(--public-surface)] p-2 shadow-xl">
                            <div class="border-b border-[var(--public-border)] px-3 py-2">
                                <p class="truncate text-sm font-semibold text-[var(--public-ink)]">{{ $currentUser?->username }}</p>
                                <p class="mt-1 truncate text-xs text-[var(--public-muted)]">{{ $currentUser?->email }}</p>
                            </div>
                            <a href="{{ route('dashboard') }}" class="{{ $mobileLink }} mt-1">{{ $copy['dashboard'] }}</a>
                            <a href="{{ route('profile.edit') }}" class="{{ $mobileLink }}">{{ __('Profil') }}</a>
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-[var(--public-border)] pt-1">
                                @csrf
                                <button type="submit" class="block w-full rounded-lg px-3 py-2.5 text-left text-sm font-semibold text-[#9F2A2A] hover:bg-red-50 dark:text-[#F0A0A0] dark:hover:bg-red-950/30">{{ __('Keluar') }}</button>
                            </form>
                        </div>
                    </details>
                @else
                    <a href="{{ route('login') }}" class="{{ $navLink }}">{{ $copy['login'] }}</a>
                    <a href="{{ route('register') }}" class="inline-flex min-h-10 items-center rounded-xl bg-[var(--public-accent)] px-4 text-xs font-semibold text-white transition hover:bg-[var(--public-accent-active)]">{{ $copy['register'] }}</a>
                @endauth
            </div>
        </div>
    </div>

    <div class="lg:hidden">
        <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="inline-flex min-w-0 items-center gap-2.5 text-[var(--public-ink)] transition hover:text-[var(--public-accent)]">
                <span class="brand-mark" aria-hidden="true">{{ $brand['mark'] }}</span>
                <span class="truncate text-base font-semibold tracking-tight">{{ $brand['name'] }}</span>
            </a>

            <div class="flex shrink-0 items-center gap-2">
                <x-theme-toggle />

                <details class="site-mobile-menu relative">
                    <summary aria-label="{{ $copy['menu'] }}" class="ui-action inline-flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-xl border border-[var(--public-border)] bg-[var(--public-surface)] text-[var(--public-ink)]">
                        <span class="sr-only">{{ $copy['menu'] }}</span>
                        <span aria-hidden="true" class="space-y-1">
                            <span class="block h-0.5 w-4 rounded-full bg-current"></span>
                            <span class="block h-0.5 w-4 rounded-full bg-current"></span>
                            <span class="block h-0.5 w-4 rounded-full bg-current"></span>
                        </span>
                    </summary>

                    <div class="fixed inset-x-0 top-16 max-h-[calc(100dvh-4rem)] overflow-y-auto border-t border-[var(--public-border)] bg-[var(--public-surface)] shadow-xl">
                        <div class="mx-auto max-w-7xl space-y-1 px-4 py-4 sm:px-6">
                            <a href="{{ route('home') }}" class="{{ $mobileLink }}">{{ __('Beranda') }}</a>
                            <a href="{{ route('places.index') }}" class="{{ $mobileLink }}">{{ $copy['destinations'] }}</a>
                            <a href="{{ route('shop.index') }}" class="{{ $mobileLink }}">{{ $copy['souvenirs'] }}</a>
                            <a href="{{ route('cart.index') }}" class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-semibold text-[var(--public-ink)] hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">
                                <span>{{ $copy['cart'] }}</span>
                                @if($cartCount > 0)
                                    <span class="rounded-full bg-[var(--public-accent)] px-2 py-0.5 text-[10px] font-semibold text-white">{{ $cartCount }}</span>
                                @endif
                            </a>
                            @auth
                                <div class="my-3 border-t border-[var(--public-border)]"></div>
                                <p class="truncate px-3 pb-1 text-xs font-semibold uppercase tracking-[0.16em] text-[var(--public-muted)]">{{ $currentUser?->username }}</p>
                                <a href="{{ route('dashboard') }}" class="{{ $mobileLink }}">{{ $copy['dashboard'] }}</a>
                                <a href="{{ route('orders.index') }}" class="{{ $mobileLink }}">{{ $copy['orders'] }}</a>
                                <a href="{{ route('profile.edit') }}" class="{{ $mobileLink }}">{{ __('Profil') }}</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full rounded-lg px-3 py-2.5 text-left text-sm font-semibold text-[#9F2A2A] dark:text-[#F0A0A0]">{{ __('Keluar') }}</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="{{ $mobileLink }}">{{ $copy['login'] }}</a>
                                <a href="{{ route('register') }}" class="{{ $mobileLink }}">{{ $copy['register'] }}</a>
                            @endauth
                            <div class="flex items-center gap-2 border-t border-[var(--public-border)] px-3 pt-4 text-xs font-semibold">
                                <a href="{{ route('lang.switch', 'id') }}" class="rounded-full border border-[var(--public-border)] px-3 py-1.5 {{ App::getLocale() === 'id' ? 'bg-[var(--public-ink)] text-[var(--public-surface)]' : 'text-[var(--public-muted)]' }}">ID</a>
                                <a href="{{ route('lang.switch', 'en') }}" class="rounded-full border border-[var(--public-border)] px-3 py-1.5 {{ App::getLocale() === 'en' ? 'bg-[var(--public-ink)] text-[var(--public-surface)]' : 'text-[var(--public-muted)]' }}">EN</a>
                            </div>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</nav>
