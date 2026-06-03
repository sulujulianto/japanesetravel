@extends('layouts.site')

@section('title', __('Katalog destinasi') . ' · ' . __('Japan Travel'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 pb-10 pt-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Destinasi') }}</p>
                <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">{{ __('Katalog destinasi') }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-300">{{ __('Cari kota, area, atau suasana perjalanan yang sesuai.') }}</p>
            </div>
            <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                {{ __('Kembali ke Beranda') }}
            </a>
        </div>

        <x-ui.card class="mt-6">
            <form method="GET" class="grid w-full gap-3 sm:grid-cols-3">
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
        </x-ui.card>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($places as $place)
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
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $place->name }}</h2>
                            <span class="shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-300">{{ $reviewCount }} {{ __('ulasan') }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ Str::limit($place->description, 120) }}</p>
                        <div class="mt-auto flex items-center justify-between gap-4 pt-4 text-xs font-medium text-slate-500 dark:text-slate-300">
                            <span>{{ Str::limit($place->address, 32) }}</span>
                            <span>{{ $place->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full">
                    <x-ui.card class="text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-300">{{ __('Belum ada destinasi yang cocok dengan filter ini.') }}</p>
                    </x-ui.card>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $places->links() }}
        </div>
    </section>
@endsection
