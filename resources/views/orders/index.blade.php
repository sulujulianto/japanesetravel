<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Akun Saya') }}</p>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Riwayat Pesanan Saya') }}</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <x-ui.alert variant="success">
                    {{ session('success') }}
                </x-ui.alert>
            @endif

            @php
                $statusVariants = [
                    'pending' => 'warning',
                    'processing' => 'info',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                ];
                $paymentVariants = [
                    'pending' => 'warning',
                    'paid' => 'success',
                    'failed' => 'danger',
                    'expired' => 'danger',
                    'refunded' => 'info',
                ];
            @endphp

            @forelse ($orders as $order)
                <x-ui.card>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Nomor Order') }}</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white">#ORDER-{{ $order->id }}</p>
                            <p class="text-sm text-slate-500">{{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Total') }}</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.badge variant="{{ $statusVariants[$order->status] ?? 'default' }}">
                                {{ __(strtoupper($order->status)) }}
                            </x-ui.badge>
                            @if($order->payment)
                                <x-ui.badge variant="{{ $paymentVariants[$order->payment->status] ?? 'default' }}">
                                    {{ strtoupper($order->payment->provider) }} · {{ __(strtoupper($order->payment->status)) }}
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="default">{{ __('Belum ada pembayaran') }}</x-ui.badge>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('orders.show', $order) }}" class="text-sm font-semibold text-rose-500 hover:text-rose-400">{{ __('Lihat Detail') }}</a>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
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
            @empty
                <x-ui.card class="text-center">
                    <p class="text-sm text-slate-500">{{ __('Belum ada riwayat pesanan.') }}</p>
                    <a href="{{ route('shop.index') }}" class="mt-3 inline-flex text-sm font-semibold text-rose-500">{{ __('Mulai Belanja') }}</a>
                </x-ui.card>
            @endforelse

            <div>
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
