@extends('layouts.site')

@section('title', __('Katalog destinasi') . ' · ' . \App\Support\Brand::name())

@section('content')
    <section class="ui-reveal mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <header class="page-hero rounded-[24px] border border-[var(--public-border)] px-6 py-9 sm:px-9 sm:py-11 lg:px-12">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div class="max-w-3xl">
                    <p class="ui-eyebrow">{{ __('Destinasi') }}</p>
                    <h1 class="ui-heading mt-3 text-4xl sm:text-5xl">{{ __('Katalog destinasi') }}</h1>
                    <p class="ui-copy mt-4 text-base">{{ __('Cari kota, area, atau suasana perjalanan yang sesuai.') }}</p>
                </div>
                <div class="w-fit rounded-xl border border-[var(--public-border)] bg-[var(--public-surface)] px-4 py-3">
                    <strong class="block text-2xl text-[var(--public-ink)]">{{ \App\Support\Format::number($places->total()) }}</strong>
                    <span class="mt-1 block text-xs font-semibold uppercase tracking-[0.14em] text-[var(--public-muted)]">{{ __('Destinasi ditemukan') }}</span>
                </div>
            </div>
        </header>

        <div class="ui-surface mt-8 rounded-[18px] p-4 sm:p-5">
            <form method="GET" class="grid w-full gap-3 lg:grid-cols-[minmax(0,1fr)_180px_190px_auto]">
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
                <x-ui.button type="submit" class="w-full lg:w-auto">{{ __('Terapkan') }}</x-ui.button>
            </form>
        </div>

        <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($places as $place)
                @php
                    $ratingValue = \App\Support\Format::rating($place->reviews_avg_rating ?? 0);
                    $reviewCount = $place->reviews_count ?? 0;
                @endphp
                <a href="{{ route('place.show', $place->slug) }}" class="ui-card ui-surface group flex h-full flex-col overflow-hidden rounded-[18px]">
                    <div class="relative h-56 overflow-hidden bg-[var(--public-surface-muted)]">
                        @if($place->image_url)
                            <img src="{{ $place->image_url }}" alt="{{ $place->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async">
                        @else
                            <img src="{{ asset('demo/place-placeholder.svg') }}" alt="{{ $place->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                        @endif
                        <div class="absolute left-4 top-4 rounded-lg bg-[var(--public-surface-elevated)] px-3 py-1 text-xs font-semibold text-[var(--public-ink)] shadow-sm">
                            {{ __('Rating') }} {{ $ratingValue }}
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex items-start justify-between gap-4">
                            <h2 class="text-lg font-semibold text-[var(--public-ink)]">{{ $place->name }}</h2>
                            <span class="shrink-0 text-xs font-semibold text-[var(--public-muted)]">{{ trans_choice('Jumlah ulasan', $reviewCount, ['count' => $reviewCount]) }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-[var(--public-muted)]">{{ Str::limit($place->description, 120) }}</p>
                        <div class="mt-auto flex items-center justify-between gap-4 pt-4 text-xs font-medium text-[var(--public-muted)]">
                            <span>{{ Str::limit($place->address, 32) }}</span>
                            <span>{{ \App\Support\Format::relative($place->created_at) }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="ui-surface col-span-full rounded-[18px] border-dashed p-10 text-center">
                    <p class="text-sm font-medium text-[var(--public-muted)]">{{ __('Belum ada destinasi yang cocok dengan filter ini.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $places->links() }}
        </div>
    </section>
@endsection
