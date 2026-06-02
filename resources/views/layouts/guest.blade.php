<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Japan Travel') }}</title>
    @includeIf('partials.theme-script')
    @includeIf('partials.vite')
</head>
<body class="auth-page font-sans antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-5 sm:px-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-sm font-semibold tracking-tight text-[var(--auth-ink)]">
                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--auth-hairline)] bg-[var(--auth-surface)] font-display text-sm">JT</span>
                <span>{{ __('Japan Travel') }}</span>
            </a>

            <div class="flex items-center gap-2 text-xs font-semibold">
                <a href="{{ route('lang.switch', 'id') }}" class="auth-control px-3 py-1.5 {{ App::getLocale() == 'id' ? 'auth-control-active' : '' }}">ID</a>
                <a href="{{ route('lang.switch', 'en') }}" class="auth-control px-3 py-1.5 {{ App::getLocale() == 'en' ? 'auth-control-active' : '' }}">EN</a>
                <button onclick="toggleTheme()" class="auth-control px-3 py-1.5" title="{{ __('Ganti tema') }}" type="button">
                    {{ __('Tema') }}
                </button>
            </div>
        </header>

        <main class="flex flex-1 items-center justify-center px-5 pb-12 pt-4 sm:px-8">
            <div class="w-full max-w-[440px]">
                <div class="mb-5 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--auth-muted)]">{{ __('Destinasi dan oleh-oleh Jepang') }}</p>
                </div>

                <section class="auth-card px-6 py-7 sm:px-8 sm:py-8">
                    {{ $slot }}
                </section>

                <p class="mx-auto mt-5 max-w-sm text-center text-xs leading-5 text-[var(--auth-muted)]">
                    {{ __('Temukan destinasi Jepang, tulis ulasan, dan kelola pesanan oleh-oleh Anda dalam satu akun.') }}
                </p>
            </div>
        </main>
    </div>
</body>
</html>
