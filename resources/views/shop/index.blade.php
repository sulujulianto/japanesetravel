@extends('layouts.site')

@section('title', __('Toko Oleh-oleh') . ' · ' . __('Japan Travel'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Oleh-oleh Jepang') }}</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-5xl">{{ __('Katalog oleh-oleh pilihan') }}</h1>
            <p class="mt-4 text-base leading-7 text-[#374151] dark:text-[#D8DEE8]">
                {{ __('Temukan produk souvenir untuk melengkapi perjalanan Anda, dari camilan khas sampai kerajinan kecil yang mudah dibawa pulang.') }}
            </p>
        </div>

        <div class="mt-10 rounded-[24px] border border-[#E7E3DC] bg-white p-4 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-5">
            <form method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1.3fr)_150px_150px_180px_170px] lg:items-end">
                <div>
                    <x-ui.label value="{{ __('Cari Produk') }}" />
                    <x-ui.input name="search" value="{{ $search }}" placeholder="{{ __('Matcha, kerajinan, fashion...') }}" />
                </div>
                <div>
                    <x-ui.label value="{{ __('Harga Minimum') }}" />
                    <x-ui.input type="number" name="min_price" value="{{ $minPrice }}" placeholder="0" />
                </div>
                <div>
                    <x-ui.label value="{{ __('Harga Maksimum') }}" />
                    <x-ui.input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="500000" />
                </div>
                <div>
                    <x-ui.label value="{{ __('Ketersediaan') }}" />
                    <x-ui.select name="availability">
                        <option value="">{{ __('Semua') }}</option>
                        <option value="in_stock" @selected($availability === 'in_stock')>{{ __('Hanya yang tersedia') }}</option>
                    </x-ui.select>
                </div>
                <div>
                    <x-ui.label value="{{ __('Urutkan') }}" />
                    <x-ui.select name="sort">
                        <option value="latest" @selected($sort === 'latest')>{{ __('Terbaru') }}</option>
                        <option value="price_low" @selected($sort === 'price_low')>{{ __('Harga Terendah') }}</option>
                        <option value="price_high" @selected($sort === 'price_high')>{{ __('Harga Tertinggi') }}</option>
                    </x-ui.select>
                </div>
                <div class="flex flex-col gap-3 border-t border-[#E7E3DC] pt-4 dark:border-[#2A333D] sm:flex-row sm:items-center lg:col-span-5">
                    <x-ui.button type="submit" class="w-full sm:w-auto">{{ __('Terapkan') }}</x-ui.button>
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-full border border-[#DDD6CC] bg-white px-5 py-2.5 text-sm font-semibold text-[#374151] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
                        {{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($souvenirs as $item)
                @php
                    $stockLabel = $item->stock <= 0 ? __('Habis') : ($item->stock <= 5 ? __('Stok rendah') : __('Tersedia'));
                    $stockClass = $item->stock <= 0
                        ? 'border-[#DDD6CC] bg-[#F1EEE8] text-[#526071] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#AEB8C7]'
                        : ($item->stock <= 5
                            ? 'border-[#D2B16F] bg-[#FFF8E6] text-[#6D541F] dark:border-[#8A6A2F] dark:bg-[#241F14] dark:text-[#D2B16F]'
                            : 'border-[#C9DDD4] bg-[#F0F8F4] text-[#245B49] dark:border-[#2F5D50] dark:bg-[#15241F] dark:text-[#8AB7A4]');
                @endphp

                <article class="group flex h-full flex-col overflow-hidden rounded-[22px] border border-[#E7E3DC] bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#DDD6CC] hover:shadow-md dark:border-[#2A333D] dark:bg-[#161B22] dark:hover:border-[#3A4652]">
                    <div class="relative aspect-square overflow-hidden bg-[#F1EEE8] dark:bg-[#1F2630]">
                        @if($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <img src="{{ asset('demo/souvenir-placeholder.svg') }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                        @endif
                        <span class="absolute left-4 top-4 rounded-full border px-3 py-1 text-xs font-semibold shadow-sm {{ $stockClass }}">
                            {{ $stockLabel }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-4">
                            <h2 class="text-base font-semibold leading-6 text-[#1F2937] dark:text-[#F4F1ED]">{{ $item->name }}</h2>
                            <span class="shrink-0 rounded-full border border-[#E7E3DC] bg-[#FAF9F6] px-2.5 py-1 text-xs font-semibold text-[#526071] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#AEB8C7]">
                                {{ __('Stok') }} {{ $item->stock }}
                            </span>
                        </div>

                        <p class="mt-3 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ Str::limit($item->description, 86) }}</p>

                        <div class="mt-auto pt-5">
                            <p class="text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            <form action="{{ route('cart.add', $item->id) }}" method="POST" class="mt-4">
                                @csrf
                                <x-ui.button type="submit" size="sm" variant="primary" class="w-full rounded-full px-4 {{ $item->stock <= 0 ? 'cursor-not-allowed opacity-70' : '' }}" :disabled="$item->stock <= 0">
                                    {{ $item->stock <= 0 ? __('Stok habis') : __('Tambah ke keranjang') }}
                                </x-ui.button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-[22px] border border-dashed border-[#DDD6CC] bg-white p-10 text-center dark:border-[#2A333D] dark:bg-[#161B22]">
                    <p class="text-sm font-medium text-[#526071] dark:text-[#D8DEE8]">{{ __('Belum ada produk yang cocok dengan filter ini.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $souvenirs->links() }}
        </div>
    </section>
@endsection
