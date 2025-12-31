<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Japan Travel') }} · Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @includeIf('partials.theme-script')
    @stack('styles')
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100">
    @php
        $adminUser = Auth::guard('admin')->user();
    @endphp

    <div x-data="{ sidebarOpen: false }" class="relative min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 right-0 h-96 w-96 rounded-full bg-rose-500/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-20 h-[28rem] w-[28rem] rounded-full bg-amber-400/10 blur-3xl"></div>
        </div>

        <div class="relative flex">
            <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full border-r border-slate-800 bg-slate-950/95 backdrop-blur-xl transition duration-300 lg:translate-x-0"
                :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
            >
                <div class="flex h-20 items-center justify-between px-6">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <span class="text-2xl">⛩️</span>
                        <span class="text-lg font-semibold tracking-tight">Japan Travel<span class="text-rose-400">.admin</span></span>
                    </a>
                    <button class="lg:hidden text-slate-400 hover:text-white" @click="sidebarOpen = false" type="button">
                        ✕
                    </button>
                </div>

                <nav class="px-4 pb-6 space-y-2 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <span>📊</span>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('admin.orders.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <span>🧾</span>
                        <span>{{ __('Pesanan') }}</span>
                    </a>
                    <a href="{{ route('admin.places.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('admin.places.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <span>🗺️</span>
                        <span>{{ __('Destinasi') }}</span>
                    </a>
                    <a href="{{ route('admin.souvenirs.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('admin.souvenirs.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <span>🛍️</span>
                        <span>{{ __('Souvenir') }}</span>
                    </a>
                    <a href="{{ route('admin.inventory.low-stock') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('admin.inventory.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        <span>📦</span>
                        <span>{{ __('Stok Rendah') }}</span>
                    </a>
                </nav>

                <div class="mt-auto px-6 pb-6 space-y-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-slate-300">
                        <div class="font-semibold text-white">{{ $adminUser?->username ?? __('Admin') }}</div>
                        <div>{{ $adminUser?->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">
                            {{ __('Keluar') }}
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col lg:pl-72">
                <header class="sticky top-0 z-30 border-b border-slate-800 bg-slate-950/80 backdrop-blur-xl">
                    <div class="flex h-20 items-center justify-between px-4 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button class="lg:hidden text-slate-200" @click="sidebarOpen = true" type="button">
                                ☰
                            </button>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('Admin Workspace') }}</p>
                                <p class="text-lg font-semibold text-white">{{ __('Pantau operasional harian') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="hidden sm:flex items-center gap-2 text-xs font-semibold">
                                <a href="{{ route('lang.switch', 'id') }}" class="rounded-full border border-white/10 px-3 py-1 {{ App::getLocale() === 'id' ? 'bg-white text-slate-900' : 'text-slate-300 hover:bg-white/10' }}">ID</a>
                                <a href="{{ route('lang.switch', 'en') }}" class="rounded-full border border-white/10 px-3 py-1 {{ App::getLocale() === 'en' ? 'bg-white text-slate-900' : 'text-slate-300 hover:bg-white/10' }}">EN</a>
                            </div>
                            <button onclick="toggleTheme()" class="h-10 w-10 rounded-full border border-white/10 text-lg text-slate-200 hover:bg-white/10" title="{{ __('Ganti tema') }}" type="button">
                                🌗
                            </button>
                            <a href="{{ route('home') }}" class="hidden sm:inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10">
                                ↗ {{ __('Lihat Situs') }}
                            </a>
                        </div>
                    </div>
                </header>

                @isset($header)
                    <div class="px-4 pt-8 lg:px-8">
                        {{ $header }}
                    </div>
                @endisset

                <main class="flex-1 px-4 pb-12 pt-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
