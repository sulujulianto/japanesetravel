@extends('layouts.site')

@section('title', __('Toko Oleh-oleh') . ' · ' . \App\Support\Brand::name())

@section('content')
    <section data-shop-catalog class="ui-reveal mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <header class="page-hero overflow-hidden rounded-[24px] border border-[var(--public-border)] px-6 py-9 sm:px-9 sm:py-11 lg:px-12">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div class="max-w-3xl">
                    <p class="ui-eyebrow text-[var(--public-secondary)]">{{ __('Oleh-oleh :region', ['region' => \App\Support\Brand::region()]) }}</p>
                    <h1 class="ui-heading mt-3 text-4xl sm:text-5xl">{{ __('Katalog oleh-oleh pilihan') }}</h1>
                    <p class="ui-copy mt-4 max-w-2xl text-base">
                        {{ __('Temukan produk souvenir untuk melengkapi perjalanan Anda, dari camilan khas sampai kerajinan kecil yang mudah dibawa pulang.') }}
                    </p>
                </div>
                <div class="w-fit rounded-xl border border-[var(--public-border)] bg-[var(--public-surface)] px-4 py-3">
                    <strong class="block text-2xl text-[var(--public-ink)]">{{ \App\Support\Format::number($souvenirs->total()) }}</strong>
                    <span class="mt-1 block text-xs font-semibold uppercase tracking-[0.14em] text-[var(--public-muted)]">{{ __('Produk ditemukan') }}</span>
                </div>
            </div>
        </header>

        <div class="mt-8 space-y-7">
            <aside aria-label="{{ __('Filter produk') }}">
                <details data-shop-filter class="shop-filter ui-surface rounded-[18px]" open>
                    <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-[var(--public-ink)] lg:hidden">
                        <span>{{ __('Filter dan urutkan') }}</span>
                        <span aria-hidden="true">＋</span>
                    </summary>

                    <div class="shop-filter-panel border-t border-[var(--public-border)] p-4 sm:p-5 lg:border-t-0">
                        <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-[minmax(0,1.4fr)_9rem_9rem_11rem_10rem_17rem] xl:items-end">
                            <div>
                                <x-ui.label for="shop-search" value="{{ __('Cari Produk') }}" />
                                <x-ui.input id="shop-search" name="search" value="{{ $search }}" placeholder="{{ __('Matcha, kerajinan, fashion...') }}" />
                            </div>

                            <div>
                                <x-ui.label for="shop-min-price" value="{{ __('Harga Minimum') }}" />
                                <x-ui.input id="shop-min-price" type="number" min="0" name="min_price" value="{{ $minPrice }}" placeholder="0" />
                            </div>

                            <div>
                                <x-ui.label for="shop-max-price" value="{{ __('Harga Maksimum') }}" />
                                <x-ui.input id="shop-max-price" type="number" min="0" name="max_price" value="{{ $maxPrice }}" placeholder="500000" />
                            </div>

                            <div>
                                <x-ui.label for="shop-availability" value="{{ __('Ketersediaan') }}" />
                                <x-ui.select id="shop-availability" name="availability">
                                    <option value="">{{ __('Semua produk') }}</option>
                                    <option value="in_stock" @selected($availability === 'in_stock')>{{ __('Hanya yang tersedia') }}</option>
                                </x-ui.select>
                            </div>

                            <div>
                                <x-ui.label for="shop-sort" value="{{ __('Urutkan') }}" />
                                <x-ui.select id="shop-sort" name="sort">
                                    <option value="latest" @selected($sort === 'latest')>{{ __('Terbaru') }}</option>
                                    <option value="price_low" @selected($sort === 'price_low')>{{ __('Harga Terendah') }}</option>
                                    <option value="price_high" @selected($sort === 'price_high')>{{ __('Harga Tertinggi') }}</option>
                                </x-ui.select>
                            </div>

                            <div class="grid grid-cols-2 gap-2 border-t border-[var(--public-border)] pt-3 sm:col-span-2 lg:col-span-1 lg:border-t-0 lg:pt-0">
                                <x-ui.button type="submit" class="w-full px-3">{{ __('Terapkan filter') }}</x-ui.button>
                                <a href="{{ route('shop.index') }}" class="ui-button-quiet w-full px-3 py-2.5 text-sm">{{ __('Hapus filter') }}</a>
                            </div>
                        </form>
                    </div>
                </details>
            </aside>

            <div class="min-w-0">
                <div class="mb-5 flex flex-col gap-2 border-b border-[var(--public-border)] pb-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="ui-heading text-xl">{{ __('Pilihan untuk Anda') }}</h2>
                        <p class="mt-1 text-sm text-[var(--public-muted)]">
                            {{ trans_choice('Menampilkan hasil produk', $souvenirs->count(), ['count' => $souvenirs->count(), 'total' => $souvenirs->total()]) }}
                        </p>
                    </div>
                    @if($search !== '')
                        <span class="w-fit rounded-lg bg-[var(--public-accent-soft)] px-3 py-1.5 text-xs font-semibold text-[var(--public-accent)]">
                            “{{ $search }}”
                        </span>
                    @endif
                </div>

                <div data-product-grid class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($souvenirs as $item)
                        @php
                            $stockLabel = $item->stock <= 0 ? __('Habis') : ($item->stock <= 5 ? __('Stok rendah') : __('Tersedia'));
                            $stockClass = $item->stock <= 0
                                ? 'bg-[var(--public-surface-muted)] text-[var(--public-muted)]'
                                : ($item->stock <= 5
                                    ? 'bg-[var(--admin-warning-soft)] text-[var(--public-warning)]'
                                    : 'bg-[var(--public-secondary-soft)] text-[var(--public-secondary)]');
                        @endphp

                        <article data-product-card class="ui-card ui-surface group flex h-full flex-col overflow-hidden rounded-[18px]">
                            <div class="relative aspect-[4/3] overflow-hidden bg-[var(--public-surface-muted)]">
                                <img
                                    src="{{ $item->image_url ?: asset('demo/souvenir-placeholder.svg') }}"
                                    alt="{{ $item->name }}"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.025]"
                                    loading="lazy"
                                    decoding="async"
                                >
                                <span class="absolute left-3 top-3 rounded-lg px-2.5 py-1 text-[11px] font-semibold shadow-sm {{ $stockClass }}">
                                    {{ $stockLabel }}
                                </span>
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-base font-semibold leading-6 text-[var(--public-ink)]">{{ $item->name }}</h3>
                                    <span class="shrink-0 text-[11px] font-semibold text-[var(--public-muted)]">{{ __('Stok') }} {{ $item->stock }}</span>
                                </div>

                                <p class="mt-2 text-sm leading-6 text-[var(--public-muted)]">{{ Str::limit($item->description, 92) }}</p>

                                <div class="mt-auto pt-5">
                                    <p class="text-xl font-bold tracking-tight text-[var(--public-ink)]">{{ \App\Support\Format::idr($item->price) }}</p>
                                    <form action="{{ route('cart.add', $item->id) }}" method="POST" class="mt-4">
                                        @csrf
                                        <x-ui.button type="submit" size="sm" variant="primary" class="w-full {{ $item->stock <= 0 ? 'cursor-not-allowed opacity-60' : '' }}" :disabled="$item->stock <= 0">
                                            {{ $item->stock <= 0 ? __('Stok habis') : __('Tambah ke keranjang') }}
                                        </x-ui.button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="ui-surface col-span-full rounded-[18px] border-dashed p-10 text-center">
                            <p class="text-sm font-medium text-[var(--public-muted)]">{{ __('Belum ada produk yang cocok dengan filter ini.') }}</p>
                            <a href="{{ route('shop.index') }}" class="mt-4 inline-flex text-sm font-semibold text-[var(--public-accent)] hover:text-[var(--public-accent-active)]">{{ __('Hapus filter') }}</a>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $souvenirs->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
