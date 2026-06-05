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
<body class="min-h-dvh bg-[#F7F5F1] font-sans text-[#1F2937] antialiased dark:bg-[#0E1116] dark:text-[#E5E7EB]">
    @php
        $adminUser = Auth::guard('admin')->user();
        $navLink = 'flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold transition';
        $navLinkIdle = 'text-[#526071] hover:bg-[#F1EEE8] hover:text-[#1F2937] dark:text-[#AEB8C7] dark:hover:bg-[#1F2630] dark:hover:text-[#F4F1ED]';
        $navLinkActive = 'bg-[#F1EEE8] text-[#8F2E2E] dark:bg-[#1F2630] dark:text-[#D96B6B]';
        $mobileLink = 'block rounded-lg px-3 py-2.5 text-sm font-semibold text-[#374151] transition hover:bg-[#F1EEE8] hover:text-[#8F2E2E] dark:text-[#D8DEE8] dark:hover:bg-[#0E1116] dark:hover:text-[#D96B6B]';
        $utilityControl = 'inline-flex h-9 items-center rounded-full border border-[#E7E3DC] px-3 text-xs font-semibold text-[#3F3F3F] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]';
    @endphp

    <div class="flex min-h-dvh flex-col lg:flex-row">
        <aside class="hidden h-dvh w-64 shrink-0 flex-col border-r border-[#E7E3DC] bg-white lg:sticky lg:top-0 lg:flex dark:border-[#2A333D] dark:bg-[#161B22]">
            <div class="border-b border-[#E7E3DC] px-5 py-5 dark:border-[#2A333D]">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2.5 text-[#222222] transition hover:text-[#8F2E2E] dark:text-[#F4F1ED] dark:hover:text-[#D96B6B]">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#DDD6CC] bg-[#FAF8F3] text-xs font-bold tracking-tight text-[#A6423A] dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#D96B6B]">JT</span>
                    <span class="text-sm font-semibold tracking-tight">
                        JapanTravel<span class="text-[#A6423A] dark:text-[#D96B6B]">.admin</span>
                    </span>
                </a>
            </div>

            <nav class="flex-1 space-y-1 px-4 py-5" aria-label="{{ __('Navigasi admin') }}">
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

            <div class="border-t border-[#E7E3DC] p-4 dark:border-[#2A333D]">
                <div class="rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] p-3 dark:border-[#2A333D] dark:bg-[#0E1116]">
                    <p class="truncate text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $adminUser?->username ?? __('Admin') }}</p>
                    <p class="mt-1 truncate text-xs text-[#526071] dark:text-[#AEB8C7]">{{ $adminUser?->email }}</p>
                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-3 border-t border-[#E7E3DC] pt-3 dark:border-[#2A333D]">
                        @csrf
                        <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-[#9F2A2A] transition hover:bg-red-50 dark:text-[#F0A0A0] dark:hover:bg-red-950/30">
                            {{ __('Keluar') }}
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <nav class="sticky top-0 z-50 border-b border-[#E7E3DC] bg-white lg:hidden dark:border-[#2A333D] dark:bg-[#161B22]">
                <details class="admin-mobile-menu">
                    <summary class="block cursor-pointer select-none">
                        <div class="flex min-h-16 items-center justify-between gap-3 px-4">
                            <span class="inline-flex min-w-0 items-center gap-2.5 text-[#222222] dark:text-[#F4F1ED]">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[#DDD6CC] bg-[#FAF8F3] text-[11px] font-bold tracking-tight text-[#A6423A] dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#D96B6B]">JT</span>
                                <span class="truncate text-sm font-semibold tracking-tight">
                                    JapanTravel<span class="text-[#A6423A] dark:text-[#D96B6B]">.admin</span>
                                </span>
                            </span>
                            <span class="flex shrink-0 items-center gap-2">
                                <button onclick="event.preventDefault(); event.stopPropagation(); toggleTheme();" class="{{ $utilityControl }}" title="{{ __('Ganti tema') }}" type="button">
                                    {{ __('Tema') }}
                                </button>
                                <span class="{{ $utilityControl }}">{{ __('Menu') }}</span>
                            </span>
                        </div>
                    </summary>

                    <div class="border-t border-[#E7E3DC] bg-white px-4 py-4 dark:border-[#2A333D] dark:bg-[#161B22]">
                        <div class="border-b border-[#E7E3DC] px-3 pb-3 dark:border-[#2A333D]">
                            <p class="truncate text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $adminUser?->username ?? __('Admin') }}</p>
                            <p class="mt-1 truncate text-xs text-[#526071] dark:text-[#AEB8C7]">{{ $adminUser?->email }}</p>
                        </div>

                        <div class="mt-2 space-y-1">
                            <a href="{{ route('admin.dashboard') }}" class="{{ $mobileLink }}">{{ __('Dashboard') }}</a>
                            <a href="{{ route('admin.orders.index') }}" class="{{ $mobileLink }}">{{ __('Pesanan') }}</a>
                            <a href="{{ route('admin.places.index') }}" class="{{ $mobileLink }}">{{ __('Destinasi') }}</a>
                            <a href="{{ route('admin.souvenirs.index') }}" class="{{ $mobileLink }}">{{ __('Souvenir') }}</a>
                            <a href="{{ route('admin.inventory.low-stock') }}" class="{{ $mobileLink }}">{{ __('Stok Rendah') }}</a>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-[#E7E3DC] px-3 pt-3 text-xs font-semibold dark:border-[#2A333D]">
                            <a href="{{ route('lang.switch', 'id') }}" class="rounded-full border border-[#E7E3DC] px-3 py-1.5 {{ App::getLocale() === 'id' ? 'bg-[#222222] text-white dark:bg-[#F4F1ED] dark:text-[#0E1116]' : 'text-[#526071] dark:border-[#2A333D] dark:text-[#AEB8C7]' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}" class="rounded-full border border-[#E7E3DC] px-3 py-1.5 {{ App::getLocale() === 'en' ? 'bg-[#222222] text-white dark:bg-[#F4F1ED] dark:text-[#0E1116]' : 'text-[#526071] dark:border-[#2A333D] dark:text-[#AEB8C7]' }}">EN</a>
                            <a href="{{ route('home') }}" class="ml-auto rounded-full border border-[#E7E3DC] px-3 py-1.5 text-[#526071] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:text-[#AEB8C7] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
                                {{ __('Lihat Situs') }}
                            </a>
                        </div>

                        <form method="POST" action="{{ route('admin.logout') }}" class="mt-3 border-t border-[#E7E3DC] px-3 pt-3 dark:border-[#2A333D]">
                            @csrf
                            <button type="submit" class="block w-full rounded-lg py-2.5 text-left text-sm font-semibold text-[#9F2A2A] transition hover:text-[#7A1F1F] dark:text-[#F0A0A0]">
                                {{ __('Keluar') }}
                            </button>
                        </form>
                    </div>
                </details>
            </nav>

            <header class="sticky top-0 z-40 hidden border-b border-[#E7E3DC] bg-white lg:block dark:border-[#2A333D] dark:bg-[#161B22]">
                <div class="mx-auto flex min-h-16 max-w-[90rem] items-center justify-between gap-6 px-6 lg:px-8">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Admin Workspace') }}</p>
                        <p class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Pantau operasional harian') }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="inline-flex items-center rounded-full border border-[#E7E3DC] bg-[#FAF8F3] p-1 text-xs font-semibold dark:border-[#2A333D] dark:bg-[#0E1116]">
                            <a href="{{ route('lang.switch', 'id') }}" class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'id' ? 'bg-[#222222] text-white dark:bg-[#F4F1ED] dark:text-[#0E1116]' : 'text-[#526071] hover:text-[#8F2E2E] dark:text-[#AEB8C7] dark:hover:text-[#D96B6B]' }}">ID</a>
                            <a href="{{ route('lang.switch', 'en') }}" class="rounded-full px-2.5 py-1 {{ App::getLocale() === 'en' ? 'bg-[#222222] text-white dark:bg-[#F4F1ED] dark:text-[#0E1116]' : 'text-[#526071] hover:text-[#8F2E2E] dark:text-[#AEB8C7] dark:hover:text-[#D96B6B]' }}">EN</a>
                        </div>
                        <button onclick="toggleTheme()" class="{{ $utilityControl }}" title="{{ __('Ganti tema') }}" type="button">
                            {{ __('Tema') }}
                        </button>
                        <a href="{{ route('home') }}" class="{{ $utilityControl }}">{{ __('Lihat Situs') }}</a>
                    </div>
                </div>
            </header>

            @isset($header)
                <div class="mx-auto w-full max-w-[90rem] px-4 pt-6 sm:px-6 lg:px-8 lg:pt-8">
                    {{ $header }}
                </div>
            @endisset

            <main class="mx-auto w-full max-w-[90rem] flex-1 px-4 pb-10 pt-6 sm:px-6 lg:px-8 lg:pb-12">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
