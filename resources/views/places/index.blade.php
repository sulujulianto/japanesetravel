@extends('layouts.site')

@section('title', __('Katalog destinasi') . ' · ' . __('Japan Travel'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#526071] dark:text-[#AEB8C7]">{{ __('Destinasi') }}</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-5xl">{{ __('Katalog destinasi') }}</h1>
            <p class="mt-4 text-base leading-7 text-[#526071] dark:text-[#D8DEE8]">{{ __('Cari kota, area, atau suasana perjalanan yang sesuai.') }}</p>
            <div class="mt-6">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-full border border-[#DDD6CC] bg-white px-5 py-2.5 text-sm font-semibold text-[#374151] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
                    {{ __('Kembali ke Beranda') }}
                </a>
            </div>
        </div>

        <div class="mt-10 rounded-[24px] border border-[#E7E3DC] bg-white p-4 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-5">
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

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($places as $place)
                @php
                    $ratingValue = $place->reviews_avg_rating ? number_format($place->reviews_avg_rating, 1) : '0.0';
                    $reviewCount = $place->reviews_count ?? 0;
                @endphp
                <a href="{{ route('place.show', $place->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-[22px] border border-[#E7E3DC] bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#DDD6CC] hover:shadow-md dark:border-[#2A333D] dark:bg-[#161B22] dark:hover:border-[#3A4652]">
                    <div class="relative h-56 overflow-hidden bg-[#F1EEE8] dark:bg-[#1F2630]">
                        @if($place->image_url)
                            <img src="{{ $place->image_url }}" alt="{{ $place->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <img src="{{ asset('demo/place-placeholder.svg') }}" alt="{{ $place->name }}" class="h-full w-full object-cover">
                        @endif
                        <div class="absolute left-4 top-4 rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#374151] shadow-sm dark:bg-[#0E1116] dark:text-[#E5E7EB]">
                            {{ __('Rating') }} {{ $ratingValue }}
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex items-start justify-between gap-4">
                            <h2 class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $place->name }}</h2>
                            <span class="shrink-0 text-xs font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ $reviewCount }} {{ __('ulasan') }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ Str::limit($place->description, 120) }}</p>
                        <div class="mt-auto flex items-center justify-between gap-4 pt-4 text-xs font-medium text-[#5F6B7A] dark:text-[#AEB8C7]">
                            <span>{{ Str::limit($place->address, 32) }}</span>
                            <span>{{ $place->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-[22px] border border-dashed border-[#DDD6CC] bg-white p-10 text-center dark:border-[#2A333D] dark:bg-[#161B22]">
                    <p class="text-sm font-medium text-[#526071] dark:text-[#D8DEE8]">{{ __('Belum ada destinasi yang cocok dengan filter ini.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $places->links() }}
        </div>
    </section>
@endsection
