<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Pesanan') }}</p>
            <h2 class="text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Riwayat pesanan') }}</h2>
            <p class="max-w-2xl text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">
                {{ __('Pantau pesanan oleh-oleh, status pembayaran, dan rincian produk yang pernah Anda checkout.') }}
            </p>
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
                    \App\Enums\OrderStatus::Pending->value => 'warning',
                    \App\Enums\OrderStatus::Processing->value => 'info',
                    \App\Enums\OrderStatus::Completed->value => 'success',
                    \App\Enums\OrderStatus::Cancelled->value => 'danger',
                ];
                $paymentVariants = [
                    \App\Enums\PaymentStatus::Pending->value => 'warning',
                    \App\Enums\PaymentStatus::Paid->value => 'success',
                    \App\Enums\PaymentStatus::Failed->value => 'danger',
                    \App\Enums\PaymentStatus::Expired->value => 'danger',
                    \App\Enums\PaymentStatus::Refunded->value => 'info',
                ];
            @endphp

            @forelse ($orders as $order)
                @php
                    $orderStatus = $order->status->value;
                    $paymentStatus = $order->payment?->status->value;
                @endphp
                <x-ui.card class="overflow-hidden p-0">
                    <div class="border-b border-slate-200/80 bg-white px-5 py-5 dark:border-slate-800 dark:bg-slate-900 sm:px-6">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Nomor Order') }}</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                                    <span class="text-sm font-medium text-[#526071] dark:text-[#AEB8C7]">{{ \App\Support\Format::date($order->created_at) }}</span>
                                </div>
                                <p class="text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">#ORDER-{{ $order->id }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.badge variant="{{ $statusVariants[$orderStatus] ?? 'default' }}">
                                        {{ __('Order') }} · {{ __(strtoupper($orderStatus)) }}
                                    </x-ui.badge>
                                    @if($order->payment)
                                        <x-ui.badge variant="{{ $paymentVariants[$paymentStatus] ?? 'default' }}">
                                            {{ __('Payment') }} · {{ strtoupper($order->payment->provider->value) }} · {{ __(strtoupper($paymentStatus)) }}
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge variant="default">{{ __('Belum ada pembayaran') }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center lg:flex-col lg:items-end">
                                <div class="sm:text-right">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Total') }}</p>
                                    <p class="text-xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ \App\Support\Format::idr($order->total_price) }}</p>
                                </div>
                                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center justify-center rounded-xl border border-[#B33A3A]/25 px-4 py-2 text-sm font-semibold text-[#B33A3A] transition hover:border-[#B33A3A] hover:bg-[#B33A3A]/5 dark:border-[#D96B6B]/30 dark:text-[#D96B6B] dark:hover:bg-[#D96B6B]/10">
                                    {{ __('Lihat detail') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 bg-[#FAF8F3]/60 px-5 py-5 dark:bg-slate-950/40 sm:px-6">
                        @foreach ($order->items as $item)
                            @php
                                $product = $item->product;
                                $productName = $product?->name ?? $item->product_name ?? __('Produk tidak tersedia');
                            @endphp
                            <div class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-900/80 sm:flex-row sm:items-center">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                    @if($item->resolved_image_url)
                                        <img src="{{ $item->resolved_image_url }}" alt="{{ $productName }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @else
                                        <img src="{{ asset('demo/souvenir-placeholder.svg') }}" alt="{{ $productName }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $productName }}</p>
                                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ $item->quantity }} x {{ \App\Support\Format::idr($item->price) }}</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Subtotal') }}</p>
                                    <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ \App\Support\Format::idr($item->quantity * $item->price) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Belum ada pesanan') }}</p>
                    <h3 class="mt-3 text-2xl font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Riwayat pesanan masih kosong') }}</h3>
                    <p class="mt-3 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">
                        {{ __('Mulai dari katalog oleh-oleh dan checkout produk yang ingin Anda kirimkan ke rumah.') }}
                    </p>
                    <a href="{{ route('shop.index') }}" class="mt-6 inline-flex items-center justify-center rounded-xl bg-[#B33A3A] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-slate-950 dark:hover:bg-[#E48787]">
                        {{ __('Belanja oleh-oleh') }}
                    </a>
                </x-ui.card>
            @endforelse

            <div>
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
