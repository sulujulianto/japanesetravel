@extends('layouts.site')

@section('title', __('Keranjang Belanja') . ' · ' . \App\Support\Brand::name())

@section('content')
    @php
        $itemCount = collect($cartItems)->sum('qty');
    @endphp

    <section class="ui-reveal mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Keranjang') }}</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-5xl">{{ __('Keranjang oleh-oleh') }}</h1>
                <p class="mt-4 text-base leading-7 text-[#374151] dark:text-[#D8DEE8]">
                    {{ __('Tinjau produk, jumlah, dan metode pembayaran sebelum melanjutkan checkout.') }}
                </p>
            </div>
            <a href="{{ route('shop.index') }}" class="inline-flex w-fit items-center justify-center rounded-full border border-[#DDD6CC] bg-white px-5 py-2.5 text-sm font-semibold text-[#374151] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
                {{ __('Lanjut belanja') }}
            </a>
        </div>

        @if(session('success'))
            <x-ui.alert variant="success" class="mt-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger" class="mt-6">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @if(count($cartItems) > 0)
            <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
                <section class="rounded-[24px] border border-[#E7E3DC] bg-white p-4 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
                    <div class="flex flex-col gap-2 border-b border-[#E7E3DC] pb-5 dark:border-[#2A333D] sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Item keranjang') }}</h2>
                            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ $itemCount }} {{ __('barang dalam keranjang') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('cart.update') }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        @foreach($cartItems as $item)
                            <article class="grid gap-4 rounded-[20px] border border-[#E7E3DC] bg-[#FAF9F6] p-4 dark:border-[#2A333D] dark:bg-[#1F2630] sm:grid-cols-[88px,minmax(0,1fr)] lg:grid-cols-[96px,minmax(0,1fr)_220px] lg:items-center">
                                <div class="h-24 w-24 overflow-hidden rounded-2xl bg-[#F1EEE8] dark:bg-[#0E1116] sm:h-22 sm:w-22 lg:h-24 lg:w-24">
                                    @if($item['product']->image_url)
                                        <img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @else
                                        <img src="{{ asset('demo/souvenir-placeholder.svg') }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <h3 class="text-base font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $item['product']->name }}</h3>
                                    <p class="mt-1 text-sm font-medium text-[#526071] dark:text-[#AEB8C7]">{{ __('Harga satuan') }}: {{ \App\Support\Format::idr($item['product']->price) }}</p>
                                    <p class="mt-2 text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Subtotal') }}: {{ \App\Support\Format::idr($item['subtotal']) }}</p>
                                </div>

                                <div class="flex flex-col gap-3 sm:col-span-2 lg:col-span-1">
                                    <div>
                                        <x-ui.label value="{{ __('Jumlah') }}" />
                                        <x-ui.input type="number" name="qty[{{ $item['product']->id }}]" value="{{ $item['qty'] }}" min="1" class="w-full" />
                                    </div>
                                    <button type="submit" form="remove-item-{{ $item['product']->id }}" class="inline-flex w-fit items-center justify-center rounded-full border border-[#E7E3DC] bg-white px-4 py-2 text-xs font-semibold text-[#9F2A2A] transition hover:border-[#9F2A2A] hover:text-[#7A1F1F] dark:border-[#2A333D] dark:bg-[#161B22] dark:text-[#F0A0A0] dark:hover:border-[#F0A0A0]" onclick="return confirm({{ Illuminate\Support\Js::from(__('Hapus barang ini?')) }});">
                                        {{ __('Hapus item') }}
                                    </button>
                                </div>
                            </article>
                        @endforeach

                        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                            <x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">{{ __('Update Keranjang') }}</x-ui.button>
                        </div>
                    </form>

                    @foreach($cartItems as $item)
                        <form id="remove-item-{{ $item['product']->id }}" action="{{ route('cart.items.destroy', $item['product']->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                </section>

                <aside class="rounded-[24px] border border-[#E7E3DC] bg-white p-5 shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6 lg:sticky lg:top-24">
                    <h2 class="text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Ringkasan belanja') }}</h2>
                    <div class="mt-5 space-y-4 border-y border-[#E7E3DC] py-5 text-sm dark:border-[#2A333D]">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[#526071] dark:text-[#AEB8C7]">{{ __('Total item') }}</span>
                            <span class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $itemCount }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[#526071] dark:text-[#AEB8C7]">{{ __('Subtotal') }}</span>
                            <span class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ \App\Support\Format::idr($total) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 pt-2 text-lg font-semibold">
                            <span class="text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Total') }}</span>
                            <span class="text-[#1F2937] dark:text-[#F4F1ED]">{{ \App\Support\Format::idr($total) }}</span>
                        </div>
                    </div>

                    <form action="{{ route('checkout.process') }}" method="POST" class="mt-6 space-y-5">
                        @csrf
                        <div>
                            <x-ui.label value="{{ __('Metode Pembayaran') }}" />
                            <div class="mt-3 space-y-3">
                                <label class="flex items-center gap-3 rounded-2xl border border-[#E7E3DC] bg-[#FAF9F6] px-4 py-3 text-sm font-medium text-[#374151] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#D8DEE8]">
                                    <input type="radio" name="payment_provider" value="midtrans" class="text-[#B33A3A] focus:ring-[#B33A3A]" checked>
                                    <span>{{ __('Midtrans (IDR)') }}</span>
                                </label>
                                <label class="flex items-center gap-3 rounded-2xl border border-[#E7E3DC] bg-[#FAF9F6] px-4 py-3 text-sm font-medium text-[#374151] dark:border-[#2A333D] dark:bg-[#1F2630] dark:text-[#D8DEE8]">
                                    <input type="radio" name="payment_provider" value="paypal" class="text-[#B33A3A] focus:ring-[#B33A3A]">
                                    <span>{{ __('PayPal (International)') }}</span>
                                </label>
                            </div>
                        </div>
                        <x-ui.button type="submit" class="w-full">{{ __('Checkout sekarang') }}</x-ui.button>
                    </form>
                </aside>
            </div>
        @else
            <section class="mt-8 rounded-[24px] border border-dashed border-[#DDD6CC] bg-white px-6 py-12 text-center shadow-sm dark:border-[#2A333D] dark:bg-[#161B22] sm:px-10">
                <div class="mx-auto max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Keranjang kosong') }}</p>
                    <h2 class="mt-3 text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Belum ada oleh-oleh di keranjang') }}</h2>
                    <p class="mt-3 text-sm leading-6 text-[#374151] dark:text-[#D8DEE8]">{{ __('Pilih produk dari katalog oleh-oleh, lalu kembali ke sini untuk meninjau pesanan sebelum checkout.') }}</p>
                    <a href="{{ route('shop.index') }}" class="mt-6 inline-flex items-center justify-center rounded-full bg-[#B33A3A] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                        {{ __('Kembali ke katalog oleh-oleh') }}
                    </a>
                </div>
            </section>
        @endif
    </section>
@endsection
