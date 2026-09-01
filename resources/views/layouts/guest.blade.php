<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Support\Brand::name() }}</title>
    @includeIf('partials.theme-script')
    @includeIf('partials.vite')
</head>
<body class="auth-page font-sans antialiased">
    <div data-public-shell class="flex min-h-dvh flex-col">
        @include('partials.site-nav')

        <main class="flex flex-1 items-center px-5 py-10 sm:px-8 sm:py-14">
            <div class="ui-reveal mx-auto grid w-full max-w-6xl gap-10 lg:grid-cols-[minmax(0,1fr)_440px] lg:items-center lg:gap-20">
                <aside class="hidden max-w-xl lg:block">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--public-accent)]">{{ __('Rencanakan perjalanan Anda') }}</p>
                    <h1 class="mt-4 font-display text-5xl font-semibold leading-tight text-[var(--public-ink)]">{{ __('Satu akun untuk perjalanan dan oleh-oleh Jepang.') }}</h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-[var(--public-muted)]">{{ __('Jelajahi destinasi, bagikan pengalaman, dan pantau pesanan Anda tanpa kehilangan konteks perjalanan.') }}</p>
                    <ul class="mt-8 grid gap-4 text-sm font-semibold text-[var(--public-ink)]">
                        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-[var(--public-accent)]" aria-hidden="true"></span>{{ __('Katalog destinasi dwibahasa') }}</li>
                        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-[var(--public-accent)]" aria-hidden="true"></span>{{ __('Ulasan dari pengguna terverifikasi') }}</li>
                        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-[var(--public-accent)]" aria-hidden="true"></span>{{ __('Riwayat pesanan dalam satu tempat') }}</li>
                    </ul>
                </aside>

                <div class="mx-auto w-full max-w-[440px]">
                <div class="mb-5 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--auth-helper)]">{{ __('Destinasi dan oleh-oleh :region', ['region' => \App\Support\Brand::region()]) }}</p>
                </div>

                <section class="auth-card px-6 py-7 sm:px-8 sm:py-8">
                    {{ $slot }}
                </section>

                <p class="mx-auto mt-5 max-w-sm text-center text-xs font-medium leading-5 text-[var(--auth-helper)]">
                    {{ __('Temukan destinasi :region, tulis ulasan, dan kelola pesanan oleh-oleh Anda dalam satu akun.', ['region' => \App\Support\Brand::region()]) }}
                </p>
                </div>
            </div>
        </main>

        @include('partials.site-footer')
    </div>
</body>
</html>
