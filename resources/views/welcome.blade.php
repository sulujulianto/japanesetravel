@extends('layouts.site')

@section('title', __('Japan Travel') . ' · ' . __('Portal Wisata Jepang'))

@section('content')
    @php
        $heroPlace = $featuredPlaces->firstWhere('image_url') ?? $featuredPlaces->first();
        $heroImageUrl = $heroPlace?->image_url;
        $usesPlaceholderHero = ! $heroImageUrl || str_contains($heroImageUrl, 'place-placeholder.svg');
    @endphp

    <section class="relative isolate overflow-hidden bg-slate-950">
        <div class="absolute inset-0">
            @if(! $usesPlaceholderHero)
                <img src="{{ $heroImageUrl }}" alt="{{ $heroPlace->name }}" class="h-full w-full object-cover">
            @else
                <div class="h-full w-full bg-[radial-gradient(circle_at_20%_20%,rgba(179,58,58,0.32),transparent_30%),radial-gradient(circle_at_80%_30%,rgba(47,93,80,0.28),transparent_34%),linear-gradient(135deg,#111827_0%,#1f2937_45%,#0e1116_100%)]"></div>
            @endif
            <div class="absolute inset-0 bg-slate-950/58"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/78 via-slate-950/34 to-slate-950/18"></div>
        </div>

        <div class="relative mx-auto flex min-h-[540px] max-w-4xl flex-col items-center justify-center px-4 py-20 text-center sm:min-h-[640px] sm:px-6 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#F4F1ED]">{{ __('Destination discovery') }}</p>
            <h1 class="mt-5 max-w-3xl text-4xl font-semibold leading-tight text-white sm:text-5xl lg:text-6xl">
                {{ __('Temukan destinasi Jepang dan oleh-oleh pilihan.') }}
            </h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-[#F4F1ED] sm:text-lg">
                {{ __('Lihat kota, ulasan, dan rekomendasi produk dalam satu tempat. Mulai dari destinasi, lalu lanjutkan ke katalog oleh-oleh saat Anda siap.') }}
            </p>
            <div class="mt-8 flex w-full flex-col justify-center gap-3 sm:w-auto sm:flex-row">
                <a href="{{ route('places.index') }}" class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">
                    {{ __('Lihat semua destinasi') }}
                </a>
                <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-full border border-white/80 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/15">
                    {{ __('Lanjutkan ke katalog oleh-oleh') }}
                </a>
            </div>
        </div>
    </section>

    <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#526071] dark:text-[#AEB8C7]">{{ __('Destinasi pilihan') }}</p>
            <h2 class="mt-3 text-3xl font-semibold text-[#1F2937] dark:text-[#F4F1ED] sm:text-4xl">{{ __('Preview destinasi') }}</h2>
            <p class="mt-3 text-sm leading-6 text-[#526071] dark:text-[#D8DEE8]">{{ __('Beberapa destinasi terbaru dari katalog. Buka katalog untuk pencarian, filter, dan pagination lengkap.') }}</p>
            <div class="mt-6">
                <a href="{{ route('places.index') }}" class="inline-flex items-center justify-center rounded-full border border-[#DDD6CC] bg-white px-5 py-2.5 text-sm font-semibold text-[#374151] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
                {{ __('Lihat semua destinasi') }}
                </a>
            </div>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($featuredPlaces as $place)
                @php
                    $ratingValue = \App\Support\Format::rating($place->reviews_avg_rating ?? 0);
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
                            <h3 class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $place->name }}</h3>
                            <span class="shrink-0 text-xs font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ trans_choice('Jumlah ulasan', $reviewCount, ['count' => $reviewCount]) }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ Str::limit($place->description, 110) }}</p>
                        <div class="mt-auto flex items-center justify-between gap-4 pt-4 text-xs font-medium text-[#5F6B7A] dark:text-[#AEB8C7]">
                            <span>{{ Str::limit($place->address, 32) }}</span>
                            <span>{{ \App\Support\Format::relative($place->created_at) }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full">
                    <x-ui.card class="text-center">
                        <p class="text-sm text-[#526071] dark:text-[#D8DEE8]">{{ __('Belum ada destinasi...') }}</p>
                    </x-ui.card>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-[24px] border border-[#E7E3DC] bg-white px-6 py-10 text-center shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:px-10">
            <div class="mx-auto max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Oleh-oleh') }}</p>
                <h3 class="mt-3 text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Lanjutkan ke katalog oleh-oleh') }}</h3>
                <p class="mt-3 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ __('Temukan produk pilihan setelah Anda melihat destinasi dan ulasan perjalanan.') }}</p>
                <div class="mt-6">
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-full bg-[#B33A3A] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                        {{ __('Lihat Katalog') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
