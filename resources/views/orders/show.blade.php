<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Detail Pesanan') }}</p>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">#ORDER-{{ $order->id }}</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <x-ui.alert variant="success">
                    {{ session('success') }}
                </x-ui.alert>
            @endif

            @if(session('error'))
                <x-ui.alert variant="danger">
                    {{ session('error') }}
                </x-ui.alert>
            @endif

            <x-ui.card>
                <div class="grid gap-6 md:grid-cols-4">
                    <div>
                        <span class="text-xs uppercase font-semibold text-slate-400">{{ __('Nomor Order') }}</span>
                        <div class="text-lg font-semibold text-slate-900 dark:text-white">#ORDER-{{ $order->id }}</div>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-semibold text-slate-400">{{ __('Tanggal') }}</span>
                        <div class="text-sm text-slate-900 dark:text-white">{{ $order->created_at->format('d M Y') }}</div>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-semibold text-slate-400">{{ __('Total') }}</span>
                        <div class="text-lg font-semibold text-slate-900 dark:text-white">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-semibold text-slate-400">{{ __('Status') }}</span>
                        <div class="text-sm text-slate-900 dark:text-white">{{ __(strtoupper($order->status)) }}</div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Informasi Pembayaran') }}</h3>
                @if($order->payment)
                    <div class="mt-4 grid gap-6 md:grid-cols-4">
                        <div>
                            <span class="text-xs uppercase font-semibold text-slate-400">{{ __('Provider') }}</span>
                            <div class="text-sm text-slate-900 dark:text-white">{{ strtoupper($order->payment->provider) }}</div>
                        </div>
                        <div>
                            <span class="text-xs uppercase font-semibold text-slate-400">{{ __('Status') }}</span>
                            <div class="text-sm text-slate-900 dark:text-white">{{ __(strtoupper($order->payment->status)) }}</div>
                        </div>
                        <div>
                            <span class="text-xs uppercase font-semibold text-slate-400">{{ __('Jumlah') }}</span>
                            <div class="text-sm text-slate-900 dark:text-white">{{ $order->payment->currency }} {{ number_format($order->payment->amount, 2, '.', ',') }}</div>
                        </div>
                        <div>
                            <span class="text-xs uppercase font-semibold text-slate-400">{{ __('Dibayar Pada') }}</span>
                            <div class="text-sm text-slate-900 dark:text-white">{{ $order->payment->paid_at?->format('d M Y H:i') ?? '-' }}</div>
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-500">{{ __('Belum ada pembayaran') }}</p>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Rincian Pesanan') }}</h3>
                <div class="mt-4 space-y-3">
                    @foreach ($order->items as $item)
                        @php
                            $product = $item->product;
                            $productName = $product?->name ?? $item->product_name ?? __('Produk tidak tersedia');
                            $productImage = $product?->image ?? $item->product_image;
                        @endphp
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200/70 bg-white/70 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="h-10 w-10 overflow-hidden rounded-lg bg-slate-200 dark:bg-slate-800">
                                @if($productImage)
                                    <img src="{{ asset('storage/' . $productImage) }}" alt="{{ $productName }}" class="h-full w-full object-cover">
                                @else
                                    <img src="{{ asset('demo/souvenir-placeholder.svg') }}" alt="{{ $productName }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $productName }}</p>
                                <p class="text-xs text-slate-500">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            @if($order->payment && in_array($order->payment->status, ['pending', 'expired', 'failed'], true) && $order->status === 'pending')
                <x-ui.card>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Bayar Sekarang') }}</h3>
                    <form action="{{ route('orders.pay', $order) }}" method="POST" class="mt-4 space-y-4">
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
                        <x-ui.button type="submit">{{ __('Bayar Sekarang') }}</x-ui.button>
                    </form>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-app-layout>
