<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Japan Travel') }} · Admin</title>

    @includeIf('partials.theme-script')
    @includeIf('partials.vite')
    @stack('styles')
</head>
<body class="min-h-dvh bg-[var(--admin-canvas)] font-sans text-[var(--admin-ink)] antialiased">
    @php
        $adminUser = Auth::guard('admin')->user();
        $navLink = 'flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold transition';
        $navLinkIdle = 'text-[var(--admin-muted)] hover:bg-[var(--admin-muted-surface)] hover:text-[var(--admin-ink)]';
        $navLinkActive = 'bg-[var(--admin-muted-surface)] text-[var(--admin-accent)]';
        $utilityControl = 'items-center rounded-full border border-[var(--admin-border)] px-3 py-2 text-xs font-semibold text-[var(--admin-muted)] transition-colors hover:text-[var(--admin-accent)]';
    @endphp

    <div class="min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-[var(--admin-border)] bg-[var(--admin-surface)] lg:flex">
            <div class="border-b border-[var(--admin-border)] px-6 py-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-3">
                    <span aria-hidden="true" class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] font-display text-sm text-[var(--admin-accent)]">JT</span>
                    <span>
                        <strong class="block text-sm">{{ config('app.name', 'Japan Travel') }}</strong>
                        <span class="mt-0.5 block text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-muted)]">Admin</span>
                    </span>
                </a>
            </div>

            <nav class="flex-1 space-y-1 px-4 py-6" aria-label="{{ __('Navigasi admin') }}">
                <a href="{{ route('admin.dashboard') }}" class="{{ $navLink }} {{ request()->routeIs('admin.dashboard') ? $navLinkActive : $navLinkIdle }}">
                    {{ __('Dashboard') }}
                </a>
                <a href="{{ route('admin.orders.index') }}" class="{{ $navLink }} {{ request()->routeIs('admin.orders.*') ? $navLinkActive : $navLinkIdle }}">
                    {{ __('Pesanan') }}
                </a>
                <a href="{{ route('admin.places.index') }}" class="{{ $navLink }} {{ request()->routeIs('admin.places.*') ? $navLinkActive : $navLinkIdle }}">
                    {{ __('Destinasi') }}
                </a>
                <a href="{{ route('admin.souvenirs.index') }}" class="{{ $navLink }} {{ request()->routeIs('admin.souvenirs.*') ? $navLinkActive : $navLinkIdle }}">
                    {{ __('Souvenir') }}
                </a>
                <a href="{{ route('admin.inventory.low-stock') }}" class="{{ $navLink }} {{ request()->routeIs('admin.inventory.*') ? $navLinkActive : $navLinkIdle }}">
                    {{ __('Stok Rendah') }}
                </a>
            </nav>

            @if ($adminUser)
                <div class="border-t border-[var(--admin-border)] p-4">
                    <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-3">
                        <p class="truncate text-sm font-semibold">{{ $adminUser->username }}</p>
                        <p class="mt-1 truncate text-xs text-[var(--admin-muted)]">{{ $adminUser->email }}</p>
                        <form method="POST" action="{{ route('admin.logout') }}" class="mt-3 border-t border-[var(--admin-border)] pt-3">
                            @csrf
                            <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-[var(--admin-accent)] transition hover:bg-[var(--admin-surface)]">
                                {{ __('Keluar') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-[var(--admin-border)] bg-[var(--admin-surface)]/95 backdrop-blur-sm">
                <div class="mx-auto flex min-h-16 max-w-[96rem] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            data-admin-menu-open
                            aria-controls="admin-mobile-navigation"
                            aria-expanded="false"
                            aria-label="{{ __('Menu') }}"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[var(--admin-border)] text-lg lg:hidden"
                            type="button"
                        >
                            <span aria-hidden="true" class="space-y-1">
                                <span class="block h-0.5 w-4 bg-current"></span>
                                <span class="block h-0.5 w-4 bg-current"></span>
                                <span class="block h-0.5 w-4 bg-current"></span>
                            </span>
                        </button>

                        <div class="min-w-0">
                            <p class="truncate text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-muted)]">{{ __('Admin Workspace') }}</p>
                            <p class="truncate text-sm font-semibold">{{ __('Pantau operasional harian') }}</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 text-xs font-semibold">
                        <div class="hidden rounded-full border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-1 sm:flex">
                            <a href="{{ route('lang.switch', 'id') }}" aria-current="{{ App::getLocale() === 'id' ? 'page' : 'false' }}" class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'id' ? 'bg-[var(--admin-ink)] text-[var(--admin-surface)]' : 'text-[var(--admin-muted)]' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}" aria-current="{{ App::getLocale() === 'en' ? 'page' : 'false' }}" class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'en' ? 'bg-[var(--admin-ink)] text-[var(--admin-surface)]' : 'text-[var(--admin-muted)]' }}">EN</a>
                        </div>
                        <button onclick="toggleTheme()" class="inline-flex {{ $utilityControl }}" title="{{ __('Ganti tema') }}" type="button">
                            {{ __('Tema') }}
                        </button>
                        <a href="{{ route('home') }}" class="hidden {{ $utilityControl }} sm:inline-flex">{{ __('Lihat Situs') }}</a>
                    </div>
                </div>
            </header>

            @isset($header)
                <div class="mx-auto w-full max-w-[96rem] px-4 pt-6 sm:px-6 lg:px-8 lg:pt-8">
                    {{ $header }}
                </div>
            @endisset

            <main class="mx-auto w-full max-w-[96rem] px-4 pb-10 pt-6 sm:px-6 lg:px-8 lg:pb-12">
                {{ $slot }}
            </main>
        </div>

        <dialog
            id="admin-mobile-navigation"
            data-admin-menu-dialog
            aria-labelledby="admin-mobile-navigation-title"
            class="admin-mobile-drawer fixed inset-y-0 left-0 m-0 h-dvh max-h-dvh w-[min(22rem,88vw)] max-w-none border-0 border-r border-[var(--admin-border)] bg-[var(--admin-surface)] p-0 text-[var(--admin-ink)] shadow-2xl lg:hidden"
        >
            <div class="flex h-full flex-col">
                <div class="flex items-center justify-between border-b border-[var(--admin-border)] px-5 py-5">
                    <strong id="admin-mobile-navigation-title" class="text-sm">{{ config('app.name', 'Japan Travel') }} · Admin</strong>
                    <button
                        data-admin-menu-close
                        aria-label="{{ __('Tutup menu') }}"
                        class="h-10 w-10 rounded-lg border border-[var(--admin-border)]"
                        type="button"
                    >×</button>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-5" aria-label="{{ __('Navigasi admin') }}">
                    <a href="{{ route('admin.dashboard') }}" class="{{ $navLink }} {{ request()->routeIs('admin.dashboard') ? $navLinkActive : $navLinkIdle }}">
                        {{ __('Dashboard') }}
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="{{ $navLink }} {{ request()->routeIs('admin.orders.*') ? $navLinkActive : $navLinkIdle }}">
                        {{ __('Pesanan') }}
                    </a>
                    <a href="{{ route('admin.places.index') }}" class="{{ $navLink }} {{ request()->routeIs('admin.places.*') ? $navLinkActive : $navLinkIdle }}">
                        {{ __('Destinasi') }}
                    </a>
                    <a href="{{ route('admin.souvenirs.index') }}" class="{{ $navLink }} {{ request()->routeIs('admin.souvenirs.*') ? $navLinkActive : $navLinkIdle }}">
                        {{ __('Souvenir') }}
                    </a>
                    <a href="{{ route('admin.inventory.low-stock') }}" class="{{ $navLink }} {{ request()->routeIs('admin.inventory.*') ? $navLinkActive : $navLinkIdle }}">
                        {{ __('Stok Rendah') }}
                    </a>

                    <div class="mt-5 flex items-center gap-2 border-t border-[var(--admin-border)] pt-5 text-xs font-semibold">
                        <a href="{{ route('lang.switch', 'id') }}" class="rounded-full border border-[var(--admin-border)] px-3 py-2">ID</a>
                        <a href="{{ route('lang.switch', 'en') }}" class="rounded-full border border-[var(--admin-border)] px-3 py-2">EN</a>
                        <a href="{{ route('home') }}" class="ml-auto text-[var(--admin-accent)]">{{ __('Lihat Situs') }}</a>
                    </div>
                </nav>

                @if ($adminUser)
                    <div class="border-t border-[var(--admin-border)] p-4">
                        <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-3">
                            <p class="truncate text-sm font-semibold">{{ $adminUser->username }}</p>
                            <p class="mt-1 truncate text-xs text-[var(--admin-muted)]">{{ $adminUser->email }}</p>
                            <form method="POST" action="{{ route('admin.logout') }}" class="mt-3 border-t border-[var(--admin-border)] pt-3">
                                @csrf
                                <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-[var(--admin-accent)] transition hover:bg-[var(--admin-surface)]">
                                    {{ __('Keluar') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </dialog>
    </div>

    @stack('scripts')
</body>
</html>
