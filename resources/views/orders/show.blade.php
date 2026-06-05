<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Detail Pesanan') }}</p>
            <h2 class="text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">#ORDER-{{ $order->id }}</h2>
            <p class="max-w-2xl text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">
                {{ __('Lihat status pesanan, pembayaran, dan rincian item oleh-oleh yang sudah dibuat dari checkout.') }}
            </p>
        </div>
    </x-slot>

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
        $canRetryPayment = $order->payment && in_array($order->payment->status, ['pending', 'expired', 'failed'], true) && $order->status === 'pending';
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
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

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <div class="space-y-6">
                    <x-ui.card class="overflow-hidden p-0">
                        <div class="border-b border-slate-200/80 bg-white px-5 py-5 dark:border-slate-800 dark:bg-slate-900 sm:px-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Ringkasan Pesanan') }}</p>
                                    <h3 class="text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">#ORDER-{{ $order->id }}</h3>
                                    <p class="text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Dibuat pada') }} {{ $order->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.badge variant="{{ $statusVariants[$order->status] ?? 'default' }}">
                                        {{ __('Order') }} · {{ __(strtoupper($order->status)) }}
                                    </x-ui.badge>
                                    @if($order->payment)
                                        <x-ui.badge variant="{{ $paymentVariants[$order->payment->status] ?? 'default' }}">
                                            {{ __('Payment') }} · {{ __(strtoupper($order->payment->status)) }}
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge variant="default">{{ __('Belum ada pembayaran') }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 bg-[#FAF8F3]/60 px-5 py-5 dark:bg-slate-950/40 sm:grid-cols-2 lg:grid-cols-4 sm:px-6">
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/80">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Tanggal') }}</span>
                                <div class="mt-2 text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $order->created_at->format('d M Y') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/80">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Status Order') }}</span>
                                <div class="mt-2 text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __(strtoupper($order->status)) }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/80">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Status Payment') }}</span>
                                <div class="mt-2 text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $order->payment ? __(strtoupper($order->payment->status)) : __('Belum ada') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/80">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Total') }}</span>
                                <div class="mt-2 text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Item Pesanan') }}</p>
                                <h3 class="mt-2 text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Rincian oleh-oleh') }}</h3>
                            </div>
                            <p class="text-sm text-[#526071] dark:text-[#AEB8C7]">{{ $order->items->count() }} {{ __('item') }}</p>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach ($order->items as $item)
                                @php
                                    $product = $item->product;
                                    $productName = $product?->name ?? $item->product_name ?? __('Produk tidak tersedia');
                                @endphp
                                <div class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/80 sm:flex-row sm:items-center">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                        @if($item->resolved_image_url)
                                            <img src="{{ $item->resolved_image_url }}" alt="{{ $productName }}" class="h-full w-full object-cover">
                                        @else
                                            <img src="{{ asset('demo/souvenir-placeholder.svg') }}" alt="{{ $productName }}" class="h-full w-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $productName }}</p>
                                        <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Qty') }} {{ $item->quantity }} · Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Subtotal') }}</p>
                                        <p class="mt-1 font-semibold text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.card>
                </div>

                <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                    <x-ui.card>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Pembayaran') }}</p>
                        <h3 class="mt-2 text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Informasi pembayaran') }}</h3>

                        @if($order->payment)
                            <div class="mt-5 space-y-4">
                                <div class="flex items-start justify-between gap-4 border-b border-slate-200/80 pb-3 dark:border-slate-800">
                                    <span class="text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Provider') }}</span>
                                    <span class="text-right text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ strtoupper($order->payment->provider) }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-4 border-b border-slate-200/80 pb-3 dark:border-slate-800">
                                    <span class="text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Status') }}</span>
                                    <x-ui.badge variant="{{ $paymentVariants[$order->payment->status] ?? 'default' }}">
                                        {{ __(strtoupper($order->payment->status)) }}
                                    </x-ui.badge>
                                </div>
                                <div class="flex items-start justify-between gap-4 border-b border-slate-200/80 pb-3 dark:border-slate-800">
                                    <span class="text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Jumlah') }}</span>
                                    <span class="text-right text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $order->payment->currency }} {{ number_format($order->payment->amount, 2, '.', ',') }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Dibayar pada') }}</span>
                                    <span class="text-right text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $order->payment->paid_at?->format('d M Y H:i') ?? '-' }}</span>
                                </div>
                            </div>
                        @else
                            <p class="mt-4 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Belum ada pembayaran untuk pesanan ini.') }}</p>
                        @endif
                    </x-ui.card>

                    <x-ui.card>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Total Pesanan') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        <p class="mt-3 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">
                            {{ __('Status pembayaran mengikuti pembaruan dari provider. Pesanan belum dianggap selesai sampai pembayaran diterima.') }}
                        </p>
                    </x-ui.card>

                    @if($canRetryPayment)
                        <x-ui.card>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Pembayaran Ulang') }}</p>
                            <h3 class="mt-2 text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Lanjutkan pembayaran') }}</h3>
                            <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">
                                {{ __('Pilih provider untuk melanjutkan pembayaran pesanan ini. Jika pembayaran lama masih pending, sistem akan mengikuti aturan retry yang tersedia.') }}
                            </p>

                            <form action="{{ route('orders.pay', $order) }}" method="POST" class="mt-5 space-y-4">
                                @csrf
                                <div>
                                    <x-ui.label value="{{ __('Metode Pembayaran') }}" />
                                    <label class="mt-3 flex items-start gap-3 rounded-xl border border-slate-200/80 bg-white p-3 text-sm text-[#374151] dark:border-slate-800 dark:bg-slate-900 dark:text-[#CBD5E1]">
                                        <input type="radio" name="payment_provider" value="midtrans" class="mt-1 text-[#B33A3A] focus:ring-[#B33A3A] dark:text-[#D96B6B] dark:focus:ring-[#D96B6B]" checked>
                                        <span>
                                            <span class="block font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Midtrans') }}</span>
                                            <span class="mt-1 block text-xs text-[#526071] dark:text-[#AEB8C7]">{{ __('Pembayaran IDR melalui provider Midtrans.') }}</span>
                                        </span>
                                    </label>
                                    <label class="mt-3 flex items-start gap-3 rounded-xl border border-slate-200/80 bg-white p-3 text-sm text-[#374151] dark:border-slate-800 dark:bg-slate-900 dark:text-[#CBD5E1]">
                                        <input type="radio" name="payment_provider" value="paypal" class="mt-1 text-[#B33A3A] focus:ring-[#B33A3A] dark:text-[#D96B6B] dark:focus:ring-[#D96B6B]">
                                        <span>
                                            <span class="block font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('PayPal') }}</span>
                                            <span class="mt-1 block text-xs text-[#526071] dark:text-[#AEB8C7]">{{ __('Pembayaran internasional melalui PayPal.') }}</span>
                                        </span>
                                    </label>
                                </div>
                                <x-ui.button type="submit" class="w-full">{{ __('Bayar sekarang') }}</x-ui.button>
                            </form>
                        </x-ui.card>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
