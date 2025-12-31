@extends('layouts.site')

@section('title', __('Keranjang Belanja') . ' · Japan Travel')

@section('content')
    <section class="mx-auto max-w-7xl px-4 pb-16 pt-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Keranjang') }}</p>
                <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">{{ __('Keranjang Belanja') }}</h1>
            </div>
            <a href="{{ route('shop.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-300">← {{ __('Lanjut Belanja') }}</a>
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
            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <x-ui.card class="lg:col-span-2">
                    <form action="{{ route('cart.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @foreach($cartItems as $item)
                            <div class="flex flex-col gap-4 rounded-2xl border border-slate-200/70 bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-950/40 sm:flex-row sm:items-center">
                                <div class="h-16 w-16 overflow-hidden rounded-xl bg-slate-200 dark:bg-slate-800">
                                    @if($item['product']->image)
                                        <img src="{{ asset('storage/' . $item['product']->image) }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover">
                                    @else
                                        <img src="{{ asset('demo/souvenir-placeholder.svg') }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $item['product']->name }}</p>
                                    <p class="text-xs text-slate-500">Rp {{ number_format($item['product']->price, 0, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-ui.input type="number" name="qty[{{ $item['product']->id }}]" value="{{ $item['qty'] }}" min="1" class="w-20" />
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                                </div>
                                <button type="submit" form="remove-item-{{ $item['product']->id }}" class="text-xs font-semibold text-rose-500 hover:text-rose-400" onclick="return confirm({{ Illuminate\Support\Js::from(__('Hapus barang ini?')) }});">
                                    {{ __('Hapus') }}
                                </button>
                            </div>
                        @endforeach

                        <div class="flex justify-end">
                            <x-ui.button type="submit" variant="secondary">{{ __('Update Keranjang') }}</x-ui.button>
                        </div>
                    </form>

                    @foreach($cartItems as $item)
                        <form id="remove-item-{{ $item['product']->id }}" action="{{ route('cart.items.destroy', $item['product']->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                </x-ui.card>

                <x-ui.card>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Ringkasan Pesanan') }}</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('Total Barang') }}</span>
                            <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-lg font-semibold">
                            <span>{{ __('Total') }}</span>
                            <span class="text-slate-900 dark:text-white">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('checkout.process') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <x-ui.label value="{{ __('Metode Pembayaran') }}" />
                            <label class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                <input type="radio" name="payment_provider" value="midtrans" class="text-rose-500 focus:ring-rose-400" checked>
                                <span>{{ __('Midtrans (IDR)') }}</span>
                            </label>
                            <label class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                <input type="radio" name="payment_provider" value="paypal" class="text-rose-500 focus:ring-rose-400">
                                <span>{{ __('PayPal (International)') }}</span>
                            </label>
                        </div>
                        <x-ui.button type="submit" class="w-full">{{ __('Checkout Sekarang') }}</x-ui.button>
                    </form>
                </x-ui.card>
            </div>
        @else
            <x-ui.card class="mt-8 text-center">
                <p class="text-sm text-slate-500">{{ __('Keranjang belanja kosong.') }}</p>
                <a href="{{ route('shop.index') }}" class="mt-4 inline-flex text-sm font-semibold text-rose-500">{{ __('Mulai Belanja') }}</a>
            </x-ui.card>
        @endif
    </section>
@endsection
