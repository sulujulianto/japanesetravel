<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Detail Pesanan') }}</p>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">#ORDER-{{ $order->id }}</h1>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-6">
            <ul class="list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Pelanggan') }}</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $order->user?->username }}</p>
                        <p class="text-sm text-slate-500">{{ $order->user?->email }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Tanggal') }}</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $order->created_at->format('d M Y H:i') }}</p>
                        <p class="mt-1 text-sm text-slate-500">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
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
                @if($order->note)
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Catatan Pesanan') }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $order->note }}</p>
                    </div>
                @endif
                @if($order->admin_note)
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Catatan Admin') }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $order->admin_note }}</p>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Item Pesanan') }}</h3>
                <div class="mt-4 space-y-4">
                    @foreach($order->items as $item)
                        @php
                            $product = $item->product;
                            $productName = $product?->name ?? $item->product_name ?? __('Produk tidak tersedia');
                            $productImage = $product?->image ?? $item->product_image;
                        @endphp
                        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/70 bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                            @if($productImage)
                                <img src="{{ asset('storage/' . $productImage) }}" alt="{{ $productName }}" class="h-14 w-14 rounded-xl object-cover">
                            @else
                                <div class="h-14 w-14 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                            @endif
                            <div class="flex-1">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $productName }}</p>
                                <p class="text-xs text-slate-500">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Riwayat Pembayaran') }}</h3>
                <div class="mt-4 space-y-3">
                    @forelse($order->payments as $payment)
                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200/70 bg-white/70 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-950/40">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ strtoupper($payment->provider) }}</p>
                                <p class="text-xs text-slate-500">{{ $payment->provider_ref }}</p>
                            </div>
                            <div>
                                <x-ui.badge variant="{{ $paymentVariants[$payment->status] ?? 'default' }}">{{ __(strtoupper($payment->status)) }}</x-ui.badge>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $payment->currency }} {{ number_format($payment->amount, 2, '.', ',') }}</p>
                                <p class="text-xs text-slate-500">{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500 dark:border-slate-700">
                            {{ __('Belum ada pembayaran untuk pesanan ini.') }}
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Update Status') }}</h3>
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-ui.label value="{{ __('Status Pesanan') }}" />
                        <x-ui.select name="status">
                            <option value="pending" @selected($order->status === 'pending')>{{ __('Menunggu') }}</option>
                            <option value="processing" @selected($order->status === 'processing')>{{ __('Diproses') }}</option>
                            <option value="completed" @selected($order->status === 'completed')>{{ __('Selesai') }}</option>
                            <option value="cancelled" @selected($order->status === 'cancelled')>{{ __('Dibatalkan') }}</option>
                        </x-ui.select>
                    </div>
                    <div>
                        <x-ui.label value="{{ __('Catatan Admin') }}" />
                        <x-ui.textarea name="admin_note" rows="3">{{ old('admin_note', $order->admin_note) }}</x-ui.textarea>
                    </div>
                    <x-ui.button type="submit">{{ __('Simpan Perubahan') }}</x-ui.button>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-admin-layout>
