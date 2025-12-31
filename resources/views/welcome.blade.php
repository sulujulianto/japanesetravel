@extends('layouts.site')

@section('title', __('Japan Travel') . ' · ' . __('Portal Wisata Jepang'))

@section('content')
    <section class="relative">
        <div class="mx-auto max-w-7xl px-4 pb-16 pt-10 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div class="space-y-6">
                    <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200 animate-rise">
                        🗺️ {{ __('Petualangan Menantimu!') }}
                    </span>
                    <h1 class="font-display text-4xl font-semibold leading-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl animate-rise" style="animation-delay: 0.1s;">
                        {{ __('Jelajahi Keajaiban') }}
                        <span class="text-rose-500">{{ __('Jepang') }}</span>
                        {{ __('tanpa ribet.') }}
                    </h1>
                    <p class="max-w-xl text-base text-slate-600 dark:text-slate-300 sm:text-lg animate-rise" style="animation-delay: 0.2s;">
                        {{ __('Temukan destinasi otentik, ulasan jujur, dan rekomendasi oleh-oleh yang benar-benar dibutuhkan traveler modern.') }}
                    </p>
                    <div class="flex flex-wrap gap-3 animate-rise" style="animation-delay: 0.3s;">
                        <a href="#explore" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 hover:bg-slate-800 dark:bg-white dark:text-slate-900">
                            {{ __('Mulai Eksplorasi') }}
                        </a>
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-200">
                            {{ __('Beli Oleh-oleh') }}
                        </a>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3 animate-rise" style="animation-delay: 0.4s;">
                        <div>
                            <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['places'] ?? 0) }}</p>
                            <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Destinasi') }}</p>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['reviews'] ?? 0) }}</p>
                            <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Ulasan') }}</p>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['souvenirs'] ?? 0) }}</p>
                            <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Souvenir') }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <x-ui.card class="overflow-hidden p-0">
                        <div class="grid gap-6 p-8 sm:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ __('Insight') }}</p>
                                <h3 class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">{{ __('Rencana perjalanan lebih presisi') }}</h3>
                                <p class="mt-3 text-sm text-slate-500 dark:text-slate-300">{{ __('Semua destinasi, ulasan, dan produk disusun agar Anda bisa fokus menikmati momen terbaik di Jepang.') }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-900 p-6 text-white">
                                <p class="text-xs uppercase tracking-[0.3em] text-rose-300">{{ __('Highlight') }}</p>
                                <p class="mt-2 text-3xl font-semibold">24/7</p>
                                <p class="mt-2 text-sm text-rose-100">{{ __('Akses rekomendasi kapan saja dari perangkat Anda.') }}</p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-3">
            <x-ui.card>
                <div class="text-3xl">⛩️</div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{{ __('Destinasi Ikonik') }}</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Rute populer dan hidden gem yang siap membuat itinerary Anda berkesan.') }}</p>
            </x-ui.card>
            <x-ui.card>
                <div class="text-3xl">🛍️</div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{{ __('Oleh-oleh Kurasi') }}</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Pilihan souvenir otentik dengan stok real-time dan harga transparan.') }}</p>
            </x-ui.card>
            <x-ui.card>
                <div class="text-3xl">💬</div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{{ __('Komunitas Traveler') }}</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Ulasan terbaru membantu Anda memilih destinasi paling cocok.') }}</p>
            </x-ui.card>
        </div>
    </section>

    <section id="explore" class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Destinasi Populer') }}</p>
                <h2 class="text-3xl font-semibold text-slate-900 dark:text-white">{{ __('Temukan spot terbaik') }}</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Filter sesuai gaya perjalanan Anda, lalu simpan favorit untuk itinerary.') }}</p>
            </div>
            <form method="GET" class="grid w-full gap-3 sm:grid-cols-3 lg:w-auto">
                <x-ui.input name="search" value="{{ $search }}" placeholder="{{ __('Cari destinasi, kota, atau aktivitas') }}" />
                <x-ui.select name="rating">
                    <option value="">{{ __('Semua Rating') }}</option>
                    <option value="4.5" @selected($rating == '4.5')>4.5+</option>
                    <option value="4" @selected($rating == '4')>4+</option>
                    <option value="3" @selected($rating == '3')>3+</option>
                </x-ui.select>
                <x-ui.select name="sort">
                    <option value="latest" @selected($sort === 'latest')>{{ __('Terbaru') }}</option>
                    <option value="rating" @selected($sort === 'rating')>{{ __('Rating Tertinggi') }}</option>
                    <option value="reviews" @selected($sort === 'reviews')>{{ __('Ulasan Terbanyak') }}</option>
                </x-ui.select>
                <div class="sm:col-span-3">
                    <x-ui.button type="submit" class="w-full">{{ __('Terapkan') }}</x-ui.button>
                </div>
            </form>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($places as $place)
                @php
                    $ratingValue = $place->reviews_avg_rating ? number_format($place->reviews_avg_rating, 1) : '0.0';
                    $reviewCount = $place->reviews_count ?? 0;
                @endphp
                <a href="{{ route('place.show', $place->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/70 bg-white/90 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/70">
                    <div class="relative h-52 overflow-hidden bg-slate-200 dark:bg-slate-800">
                        @if($place->image)
                            <img src="{{ asset('storage/' . $place->image) }}" alt="{{ $place->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <img src="{{ asset('demo/place-placeholder.svg') }}" alt="{{ $place->name }}" class="h-full w-full object-cover">
                        @endif
                        <div class="absolute left-4 top-4 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-slate-700 backdrop-blur dark:bg-slate-950/70 dark:text-slate-200">
                            ⭐ {{ $ratingValue }} · {{ $reviewCount }} {{ __('ulasan') }}
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $place->name }}</h3>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ Str::limit($place->description, 120) }}</p>
                        <div class="mt-auto flex items-center justify-between pt-4 text-xs text-slate-400">
                            <span>📍 {{ Str::limit($place->address, 32) }}</span>
                            <span>{{ $place->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full">
                    <x-ui.card class="text-center">
                        <p class="text-sm text-slate-500">{{ __('Belum ada destinasi...') }}</p>
                    </x-ui.card>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $places->links() }}
        </div>
    </section>

    <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-ui.card class="flex flex-col items-start justify-between gap-6 bg-slate-900 text-white sm:flex-row sm:items-center">
            <div>
                <h3 class="text-2xl font-semibold">{{ __('Siap belanja oleh-oleh?') }}</h3>
                <p class="mt-2 text-sm text-slate-200">{{ __('Pilih produk terbaik langsung dari Jepang dengan checkout instan.') }}</p>
            </div>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900">
                {{ __('Lihat Katalog') }}
            </a>
        </x-ui.card>
    </section>
@endsection
