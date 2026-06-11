@extends('layouts.site')

@section('title', __('Japan Travel') . ' · ' . __('Portal Wisata Jepang'))

@section('content')
    @php
        $isEnglish = app()->getLocale() === 'en';
        $proofItems = $isEnglish
            ? ['Bilingual catalog', 'Verified-user reviews', 'Souvenir checkout']
            : ['Katalog dwibahasa', 'Ulasan pengguna terverifikasi', 'Checkout oleh-oleh'];
        $previewDescription = $isEnglish
            ? 'Start with a few recent destinations, then use the full catalog to search and compare places.'
            : 'Mulai dari beberapa destinasi terbaru, lalu gunakan katalog lengkap untuk mencari dan membandingkan tempat.';
        $portfolioNote = $isEnglish
            ? 'Portfolio demo: travel inquiries and payment providers require separate configuration.'
            : 'Demo portfolio: konsultasi perjalanan dan provider pembayaran memerlukan konfigurasi terpisah.';
    @endphp

    <section class="border-b border-[#E7E3DC] bg-[#FAF8F4] dark:border-[#2A333D] dark:bg-[#11161D]">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-16 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,0.85fr)] lg:items-center lg:gap-14 lg:px-8 lg:py-20">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#8F2E2E] dark:text-[#D96B6B]">
                    {{ __('Destination discovery') }}
                </p>
                <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-5xl lg:text-[3.5rem]">
                    {{ __('Temukan destinasi Jepang dan oleh-oleh pilihan.') }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-[#526071] dark:text-[#CBD5E1] sm:text-lg">
                    {{ __('Lihat kota, ulasan, dan rekomendasi produk dalam satu tempat. Mulai dari destinasi, lalu lanjutkan ke katalog oleh-oleh saat Anda siap.') }}
                </p>

                <div class="mt-7 flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <a href="{{ route('places.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-full bg-[#B33A3A] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] focus:outline-none focus:ring-2 focus:ring-[#B33A3A] focus:ring-offset-2 dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484] dark:focus:ring-offset-[#11161D]">
                        {{ __('Lihat semua destinasi') }}
                    </a>
                    <a href="{{ route('shop.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-[#CFC7BB] bg-white px-6 py-3 text-sm font-semibold text-[#374151] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] focus:outline-none focus:ring-2 focus:ring-[#B33A3A] focus:ring-offset-2 dark:border-[#3A4652] dark:bg-[#181E26] dark:text-[#E5E7EB] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B] dark:focus:ring-offset-[#11161D]">
                        {{ __('Lanjutkan ke katalog oleh-oleh') }}
                    </a>
                </div>

                <ul class="mt-7 flex flex-wrap gap-x-5 gap-y-3 text-sm font-medium text-[#526071] dark:text-[#AEB8C7]" aria-label="{{ $isEnglish ? 'Store features' : 'Fitur layanan' }}">
                    @foreach($proofItems as $item)
                        <li class="inline-flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#2F5D50] dark:bg-[#8AB7A4]" aria-hidden="true"></span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="min-w-0 rounded-[22px] border border-[#DDD6CC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#181E26] sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#526071] dark:text-[#AEB8C7]">
                            {{ __('Destinasi pilihan') }}
                        </p>
                        <p class="mt-1 text-sm text-[#374151] dark:text-[#D8DEE8]">
                            {{ $isEnglish ? 'A quick look at the current catalog.' : 'Sekilas dari katalog saat ini.' }}
                        </p>
                    </div>
                    <span class="rounded-full bg-[#EEF3EF] px-3 py-1 text-xs font-semibold text-[#2F5D50] dark:bg-[#21302B] dark:text-[#8AB7A4]">
                        {{ \App\Support\Format::number($featuredPlaces->count()) }}
                    </span>
                </div>

                <div class="mt-5 divide-y divide-[#E7E3DC] dark:divide-[#2A333D]">
                    @forelse($featuredPlaces->take(3) as $place)
                        <div class="flex min-w-0 items-center gap-4 py-4 first:pt-0 last:pb-0">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F4ECE8] text-sm font-semibold text-[#8F2E2E] dark:bg-[#342522] dark:text-[#D96B6B]" aria-hidden="true">
                                {{ str($place->name)->substr(0, 1)->upper() }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $place->name }}</p>
                                <p class="mt-1 truncate text-xs text-[#5F6B7A] dark:text-[#AEB8C7]">{{ $place->address }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-5 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Belum ada destinasi...') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto mt-14 max-w-7xl px-4 sm:mt-16 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#526071] dark:text-[#AEB8C7]">{{ __('Destinasi pilihan') }}</p>
                <h2 class="mt-3 text-3xl font-semibold text-[#1F2937] dark:text-[#F4F1ED] sm:text-4xl">{{ __('Preview destinasi') }}</h2>
                <p class="mt-3 text-sm leading-6 text-[#526071] dark:text-[#D8DEE8]">{{ $previewDescription }}</p>
            </div>
            <a href="{{ route('places.index') }}" class="inline-flex w-fit items-center text-sm font-semibold text-[#8F2E2E] transition hover:text-[#B33A3A] focus:outline-none focus:ring-2 focus:ring-[#B33A3A] focus:ring-offset-4 dark:text-[#D96B6B] dark:hover:text-[#E18484] dark:focus:ring-offset-[#0E1116]">
                {{ __('Lihat semua destinasi') }}
                <span class="ml-2" aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($featuredPlaces as $place)
                @php
                    $ratingValue = \App\Support\Format::rating($place->reviews_avg_rating ?? 0);
                    $reviewCount = $place->reviews_count ?? 0;
                @endphp
                <a href="{{ route('place.show', $place->slug) }}" class="group flex h-full min-w-0 flex-col overflow-hidden rounded-[20px] border border-[#E7E3DC] bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-[#D5CDC1] hover:shadow-md dark:border-[#2A333D] dark:bg-[#161B22] dark:hover:border-[#3A4652]">
                    <div class="relative h-44 overflow-hidden bg-[#F1EEE8] dark:bg-[#1F2630]">
                        @if($place->image_url)
                            <img src="{{ $place->image_url }}" alt="{{ $place->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                        @else
                            <img src="{{ asset('demo/place-placeholder.svg') }}" alt="{{ $place->name }}" class="h-full w-full object-cover">
                        @endif
                        <div class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-[#374151] shadow-sm dark:bg-[#0E1116]/95 dark:text-[#E5E7EB]">
                            {{ __('Rating') }} {{ $ratingValue }}
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex min-w-0 items-start justify-between gap-3">
                            <h3 class="min-w-0 text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $place->name }}</h3>
                            <span class="shrink-0 text-xs font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ trans_choice('Jumlah ulasan', $reviewCount, ['count' => $reviewCount]) }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ Str::limit($place->description, 100) }}</p>
                        <div class="mt-auto flex min-w-0 items-center justify-between gap-3 pt-4 text-xs font-medium text-[#5F6B7A] dark:text-[#AEB8C7]">
                            <span class="min-w-0 truncate">{{ $place->address }}</span>
                            <span class="shrink-0 text-[#8F2E2E] dark:text-[#D96B6B]" aria-hidden="true">&rarr;</span>
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

    <section class="mx-auto mt-14 max-w-7xl px-4 sm:mt-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 rounded-[22px] border border-[#DDE5DF] bg-[#F2F6F3] px-6 py-8 dark:border-[#2D3C36] dark:bg-[#17201D] sm:px-8 sm:py-10 lg:grid-cols-[1fr_auto] lg:items-center">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Oleh-oleh') }}</p>
                <h2 class="mt-3 text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED] sm:text-3xl">{{ __('Lanjutkan ke katalog oleh-oleh') }}</h2>
                <p class="mt-3 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ __('Temukan produk pilihan setelah Anda melihat destinasi dan ulasan perjalanan.') }}</p>
            </div>
            <a href="{{ route('shop.index') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-full bg-[#2F5D50] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#24493F] focus:outline-none focus:ring-2 focus:ring-[#2F5D50] focus:ring-offset-2 dark:bg-[#8AB7A4] dark:text-[#0E1116] dark:hover:bg-[#A2C7B7] dark:focus:ring-offset-[#17201D] sm:w-fit">
                {{ __('Lihat Katalog') }}
            </a>
        </div>
    </section>

    <aside class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="{{ $isEnglish ? 'Portfolio note' : 'Catatan portfolio' }}">
        <p class="rounded-xl border border-[#E7E3DC] bg-white px-4 py-3 text-center text-xs leading-5 text-[#5F6B7A] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#AEB8C7]">
            {{ $portfolioNote }}
        </p>
    </aside>
@endsection
