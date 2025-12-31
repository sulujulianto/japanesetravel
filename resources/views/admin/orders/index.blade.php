<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Manajemen Order') }}</p>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Daftar Pesanan') }}</h1>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-ui.card>
        <form method="GET" class="grid gap-4 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <x-ui.label value="{{ __('Cari') }}" />
                <x-ui.input name="q" value="{{ $search }}" placeholder="{{ __('ID order, email, atau nama') }}" />
            </div>
            <div>
                <x-ui.label value="{{ __('Status Order') }}" />
                <x-ui.select name="status">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="pending" @selected($status === 'pending')>{{ __('Menunggu') }}</option>
                    <option value="processing" @selected($status === 'processing')>{{ __('Diproses') }}</option>
                    <option value="completed" @selected($status === 'completed')>{{ __('Selesai') }}</option>
                    <option value="cancelled" @selected($status === 'cancelled')>{{ __('Dibatalkan') }}</option>
                </x-ui.select>
            </div>
            <div>
                <x-ui.label value="{{ __('Status Payment') }}" />
                <x-ui.select name="payment_status">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="unpaid" @selected($paymentStatus === 'unpaid')>{{ __('Belum ada pembayaran') }}</option>
                    <option value="pending" @selected($paymentStatus === 'pending')>{{ __('Pending') }}</option>
                    <option value="paid" @selected($paymentStatus === 'paid')>{{ __('Paid') }}</option>
                    <option value="failed" @selected($paymentStatus === 'failed')>{{ __('Failed') }}</option>
                    <option value="expired" @selected($paymentStatus === 'expired')>{{ __('Expired') }}</option>
                    <option value="refunded" @selected($paymentStatus === 'refunded')>{{ __('Refunded') }}</option>
                </x-ui.select>
            </div>
            <div>
                <x-ui.label value="{{ __('Dari') }}" />
                <x-ui.input type="date" name="date_from" value="{{ $dateFrom }}" />
            </div>
            <div>
                <x-ui.label value="{{ __('Sampai') }}" />
                <x-ui.input type="date" name="date_to" value="{{ $dateTo }}" />
            </div>
            <div class="flex items-end gap-2 lg:col-span-6">
                <x-ui.button type="submit">{{ __('Terapkan Filter') }}</x-ui.button>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                    {{ __('Reset') }}
                </a>
            </div>
        </form>
    </x-ui.card>

    <div class="mt-6 overflow-hidden">
        <x-ui.card>
            <div class="overflow-x-auto">
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
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-slate-400">
                            <th class="pb-3">{{ __('Order') }}</th>
                            <th class="pb-3">{{ __('Pelanggan') }}</th>
                            <th class="pb-3">{{ __('Tanggal') }}</th>
                            <th class="pb-3">{{ __('Total') }}</th>
                            <th class="pb-3">{{ __('Pembayaran') }}</th>
                            <th class="pb-3">{{ __('Status') }}</th>
                            <th class="pb-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800">
                        @forelse($orders as $order)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900 dark:text-white">#ORDER-{{ $order->id }}</td>
                                <td class="py-3">
                                    <div class="text-slate-900 dark:text-white">{{ $order->user?->username }}</div>
                                    <div class="text-xs text-slate-500">{{ $order->user?->email }}</div>
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-300">{{ $order->created_at->format('d M Y') }}</td>
                                <td class="py-3 text-slate-900 dark:text-white">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td class="py-3">
                                    @if($order->payment)
                                        <x-ui.badge variant="{{ $paymentVariants[$order->payment->status] ?? 'default' }}">
                                            {{ strtoupper($order->payment->provider) }} · {{ __(strtoupper($order->payment->status)) }}
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge variant="default">{{ __('Belum ada') }}</x-ui.badge>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <x-ui.badge variant="{{ $statusVariants[$order->status] ?? 'default' }}">
                                        {{ __(strtoupper($order->status)) }}
                                    </x-ui.badge>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-semibold text-rose-500 hover:text-rose-400">{{ __('Detail') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-sm text-slate-500">
                                    {{ __('Belum ada pesanan yang sesuai.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </x-ui.card>
    </div>
</x-admin-layout>
