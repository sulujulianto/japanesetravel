@extends('layouts.site')

@section('title', __('Japan Travel') . ' · ' . __('Portal Wisata Jepang'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 pb-12 pt-10 sm:px-6 lg:px-8">
        <div class="grid items-center gap-10 lg:grid-cols-[1fr_0.9fr]">
            <div class="space-y-6">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-300">{{ __('Destination discovery') }}</p>
                <h1 class="max-w-3xl text-4xl font-semibold leading-tight text-slate-900 dark:text-white sm:text-5xl">
                    {{ __('Temukan destinasi Jepang dan oleh-oleh pilihan.') }}
                </h1>
                <p class="max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">
                    {{ __('Lihat kota, ulasan, dan rekomendasi produk dalam satu tempat. Mulai dari destinasi, lalu lanjutkan ke katalog oleh-oleh saat Anda siap.') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('places.index') }}" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900">
                        {{ __('Lihat semua destinasi') }}
                    </a>
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-200">
                        {{ __('Lanjutkan ke katalog oleh-oleh') }}
                    </a>
                </div>
            </div>

            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">{{ __('Ringkasan') }}</p>
                <dl class="mt-5 grid grid-cols-3 gap-4">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-400">{{ __('Destinasi') }}</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['places'] ?? 0) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-400">{{ __('Ulasan') }}</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['reviews'] ?? 0) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-400">{{ __('Souvenir') }}</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['souvenirs'] ?? 0) }}</dd>
                    </div>
                </dl>
                <p class="mt-5 text-sm leading-6 text-slate-500 dark:text-slate-300">
                    {{ __('Places adalah katalog destinasi dan ulasan. Pembelian tiket belum menjadi fitur aktif.') }}
                </p>
            </x-ui.card>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">{{ __('Destinasi pilihan') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ __('Preview destinasi') }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-300">{{ __('Beberapa destinasi terbaru dari katalog. Buka katalog untuk pencarian, filter, dan pagination lengkap.') }}</p>
            </div>
            <a href="{{ route('places.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-200">
                {{ __('Lihat semua destinasi') }}
            </a>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($featuredPlaces as $place)
                @php
                    $ratingValue = $place->reviews_avg_rating ? number_format($place->reviews_avg_rating, 1) : '0.0';
                    $reviewCount = $place->reviews_count ?? 0;
                @endphp
                <a href="{{ route('place.show', $place->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/70 bg-white/90 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/70">
                    <div class="relative h-52 overflow-hidden bg-slate-200 dark:bg-slate-800">
                        @if($place->image_url)
                            <img src="{{ $place->image_url }}" alt="{{ $place->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <img src="{{ asset('demo/place-placeholder.svg') }}" alt="{{ $place->name }}" class="h-full w-full object-cover">
                        @endif
                        <div class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-950/80 dark:text-slate-200">
                            {{ __('Rating') }} {{ $ratingValue }}
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $place->name }}</h3>
                            <span class="shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-300">{{ $reviewCount }} {{ __('ulasan') }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ Str::limit($place->description, 110) }}</p>
                        <div class="mt-auto flex items-center justify-between gap-4 pt-4 text-xs font-medium text-slate-500 dark:text-slate-300">
                            <span>{{ Str::limit($place->address, 32) }}</span>
                            <span>{{ $place->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full">
                    <x-ui.card class="text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-300">{{ __('Belum ada destinasi...') }}</p>
                    </x-ui.card>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-ui.card class="flex flex-col items-start justify-between gap-6 bg-slate-900 text-white sm:flex-row sm:items-center">
            <div>
                <h3 class="text-2xl font-semibold">{{ __('Lanjutkan ke katalog oleh-oleh') }}</h3>
                <p class="mt-2 text-sm text-slate-200">{{ __('Temukan produk pilihan setelah Anda melihat destinasi dan ulasan perjalanan.') }}</p>
            </div>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900">
                {{ __('Lihat Katalog') }}
            </a>
        </x-ui.card>
    </section>
@endsection
