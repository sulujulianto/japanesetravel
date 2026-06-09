<x-admin-layout>
    <x-slot name="header">
        <div class="rounded-2xl border border-[#E7E3DC] bg-white p-5 dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Manajemen Order') }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-3xl">{{ __('Daftar Pesanan') }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Cari dan pantau status pesanan serta pembayaran pelanggan dari satu daftar operasional.') }}</p>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @php
        $fieldClass = 'mt-2 w-full rounded-xl border border-[#DDD6CC] bg-white px-3.5 py-2.5 text-sm text-[#1F2937] outline-none transition placeholder:text-[#667085] focus:border-[#B33A3A] focus:ring-2 focus:ring-[#B33A3A]/15 dark:border-[#2A333D] dark:bg-[#0E1116] dark:text-[#F4F1ED] dark:placeholder:text-[#AEB8C7] dark:focus:border-[#D96B6B] dark:focus:ring-[#D96B6B]/20';
        $labelClass = 'block text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:text-[#AEB8C7]';
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

    <section class="rounded-2xl border border-[#E7E3DC] bg-white p-5 dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6" aria-labelledby="order-filters-heading">
        <div>
            <h2 id="order-filters-heading" class="text-base font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Filter Pesanan') }}</h2>
            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Gunakan satu atau beberapa filter untuk mempersempit daftar operasional.') }}</p>
        </div>

        <form method="GET" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <div class="sm:col-span-2 xl:col-span-2">
                <label for="order-search" class="{{ $labelClass }}">{{ __('Cari') }}</label>
                <input id="order-search" name="q" value="{{ $search }}" placeholder="{{ __('ID order, email, atau nama') }}" class="{{ $fieldClass }}">
            </div>
            <div>
                <label for="order-status" class="{{ $labelClass }}">{{ __('Status Order') }}</label>
                <select id="order-status" name="status" class="{{ $fieldClass }}">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="pending" @selected($status === 'pending')>{{ __('Menunggu') }}</option>
                    <option value="processing" @selected($status === 'processing')>{{ __('Diproses') }}</option>
                    <option value="completed" @selected($status === 'completed')>{{ __('Selesai') }}</option>
                    <option value="cancelled" @selected($status === 'cancelled')>{{ __('Dibatalkan') }}</option>
                </select>
            </div>
            <div>
                <label for="payment-status" class="{{ $labelClass }}">{{ __('Status Payment') }}</label>
                <select id="payment-status" name="payment_status" class="{{ $fieldClass }}">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="unpaid" @selected($paymentStatus === 'unpaid')>{{ __('Belum ada pembayaran') }}</option>
                    <option value="pending" @selected($paymentStatus === 'pending')>{{ __('Pending') }}</option>
                    <option value="paid" @selected($paymentStatus === 'paid')>{{ __('Paid') }}</option>
                    <option value="failed" @selected($paymentStatus === 'failed')>{{ __('Failed') }}</option>
                    <option value="expired" @selected($paymentStatus === 'expired')>{{ __('Expired') }}</option>
                    <option value="refunded" @selected($paymentStatus === 'refunded')>{{ __('Refunded') }}</option>
                </select>
            </div>
            <div>
                <label for="date-from" class="{{ $labelClass }}">{{ __('Dari') }}</label>
                <input id="date-from" type="date" name="date_from" value="{{ $dateFrom }}" class="{{ $fieldClass }}">
            </div>
            <div>
                <label for="date-to" class="{{ $labelClass }}">{{ __('Sampai') }}</label>
                <input id="date-to" type="date" name="date_to" value="{{ $dateTo }}" class="{{ $fieldClass }}">
            </div>
            <div class="flex flex-col gap-2 border-t border-[#E7E3DC] pt-4 sm:col-span-2 sm:flex-row sm:items-center xl:col-span-6 dark:border-[#2A333D]">
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[#B33A3A] px-4 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                    {{ __('Terapkan Filter') }}
                </button>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#E7E3DC] px-4 text-sm font-semibold text-[#526071] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:text-[#AEB8C7] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
                    {{ __('Reset') }}
                </a>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-2xl border border-[#E7E3DC] bg-white dark:border-[#2A333D] dark:bg-[#161B22]" aria-labelledby="orders-list-heading">
        <div class="border-b border-[#E7E3DC] px-5 py-4 dark:border-[#2A333D] sm:px-6">
            <h2 id="orders-list-heading" class="text-base font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Hasil Pesanan') }}</h2>
            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Pesanan terbaru ditampilkan lebih dahulu dengan status operasional dan pembayaran terkini.') }}</p>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#E7E3DC] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:border-[#2A333D] dark:text-[#AEB8C7]">
                        <th class="whitespace-nowrap px-6 py-3">{{ __('Order') }}</th>
                        <th class="px-4 py-3">{{ __('Pelanggan') }}</th>
                        <th class="whitespace-nowrap px-4 py-3">{{ __('Tanggal') }}</th>
                        <th class="whitespace-nowrap px-4 py-3">{{ __('Total') }}</th>
                        <th class="px-4 py-3">{{ __('Pembayaran') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E3DC] dark:divide-[#2A333D]">
                    @forelse($orders as $order)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-[#1F2937] dark:text-[#F4F1ED]">#ORDER-{{ $order->id }}</td>
                            <td class="px-4 py-4">
                                <p class="font-medium text-[#374151] dark:text-[#D8DEE8]">{{ $order->user?->username ?: __('Pengguna tidak tersedia') }}</p>
                                <p class="mt-1 text-xs text-[#526071] dark:text-[#AEB8C7]">{{ $order->user?->email ?: '-' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-[#526071] dark:text-[#AEB8C7]">{{ \App\Support\Format::date($order->created_at) }}</td>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ \App\Support\Format::idr($order->total_price) }}</td>
                            <td class="px-4 py-4">
                                @if($order->payment)
                                    <x-ui.badge variant="{{ $paymentVariants[$order->payment->status] ?? 'default' }}">
                                        {{ strtoupper($order->payment->provider) }} · {{ __(strtoupper($order->payment->status)) }}
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="default">{{ __('Belum ada') }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <x-ui.badge variant="{{ $statusVariants[$order->status] ?? 'default' }}">
                                    {{ __(strtoupper($order->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-semibold text-[#B33A3A] transition hover:text-[#8F2E2E] dark:text-[#D96B6B] dark:hover:text-[#E18484]">{{ __('Detail') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center">
                                <p class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Belum ada pesanan yang sesuai.') }}</p>
                                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Ubah atau reset filter untuk melihat pesanan lainnya.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse($orders as $order)
                <article class="rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] p-4 dark:border-[#2A333D] dark:bg-[#0E1116]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">#ORDER-{{ $order->id }}</p>
                            <p class="mt-1 truncate text-sm text-[#526071] dark:text-[#AEB8C7]">{{ $order->user?->username ?: __('Pengguna tidak tersedia') }}</p>
                        </div>
                        <a href="{{ route('admin.orders.show', $order) }}" class="shrink-0 text-sm font-semibold text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Detail') }}</a>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 border-y border-[#E7E3DC] py-3 dark:border-[#2A333D]">
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Tanggal') }}</dt>
                            <dd class="mt-1 text-sm text-[#374151] dark:text-[#D8DEE8]">{{ \App\Support\Format::date($order->created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Total') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ \App\Support\Format::idr($order->total_price) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if($order->payment)
                            <x-ui.badge variant="{{ $paymentVariants[$order->payment->status] ?? 'default' }}">
                                {{ strtoupper($order->payment->provider) }} · {{ __(strtoupper($order->payment->status)) }}
                            </x-ui.badge>
                        @else
                            <x-ui.badge variant="default">{{ __('Belum ada pembayaran') }}</x-ui.badge>
                        @endif
                        <x-ui.badge variant="{{ $statusVariants[$order->status] ?? 'default' }}">
                            {{ __(strtoupper($order->status)) }}
                        </x-ui.badge>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-[#E7E3DC] p-6 text-center dark:border-[#2A333D]">
                    <p class="text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Belum ada pesanan yang sesuai.') }}</p>
                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Ubah atau reset filter untuk melihat pesanan lainnya.') }}</p>
                </div>
            @endforelse
        </div>

        @if($orders->hasPages())
            <div class="border-t border-[#E7E3DC] px-4 py-4 dark:border-[#2A333D] sm:px-6">
                {{ $orders->links() }}
            </div>
        @endif
    </section>
</x-admin-layout>
