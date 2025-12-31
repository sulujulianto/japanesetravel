@extends('layouts.site')

@section('title', __('Toko Oleh-oleh') . ' · ' . __('Japan Travel'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 pb-10 pt-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Toko Oleh-oleh') }}</p>
                <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">{{ __('Bawa pulang rasa Jepang') }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Kurasi souvenir autentik dengan stok real-time dan pengiriman aman.') }}</p>
            </div>
        </div>

        <x-ui.card class="mt-6">
            <form method="GET" class="grid gap-4 lg:grid-cols-6">
                <div class="lg:col-span-2">
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
                <div class="lg:col-span-6 flex gap-3">
                    <x-ui.button type="submit">{{ __('Terapkan') }}</x-ui.button>
                    <a href="{{ route('shop.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-300">{{ __('Reset') }}</a>
                </div>
            </form>
        </x-ui.card>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($souvenirs as $item)
                <div class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/70 bg-white/90 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/70">
                    <div class="relative h-52 overflow-hidden bg-slate-200 dark:bg-slate-800">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <img src="{{ asset('demo/souvenir-placeholder.svg') }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                        @endif
                        @if($item->stock <= 0)
                            <span class="absolute left-4 top-4 rounded-full bg-slate-900/80 px-3 py-1 text-xs font-semibold text-white">{{ __('HABIS') }}</span>
                        @elseif($item->stock <= 5)
                            <span class="absolute left-4 top-4 rounded-full bg-amber-400/90 px-3 py-1 text-xs font-semibold text-slate-900">{{ __('TERBATAS') }}</span>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ $item->name }}</h3>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-300">{{ Str::limit($item->description, 80) }}</p>
                        <div class="mt-auto pt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-white">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                    <p class="text-[10px] text-slate-400">{{ __('Stok') }}: {{ $item->stock }}</p>
                                </div>
                                <form action="{{ route('cart.add', $item->id) }}" method="POST">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="primary" class="rounded-full px-4 {{ $item->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}" @disabled($item->stock <= 0)>
                                        {{ __('Tambah') }}
                                    </x-ui.button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-ui.card class="text-center">
                        <p class="text-sm text-slate-500">{{ __('Belum ada barang...') }}</p>
                    </x-ui.card>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $souvenirs->links() }}
        </div>
    </section>
@endsection
