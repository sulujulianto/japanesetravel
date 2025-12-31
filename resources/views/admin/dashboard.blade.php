<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 rounded-3xl border border-slate-200/60 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/60 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ __('Ringkasan Operasional') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Dashboard Admin') }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">{{ __('Pantau penjualan, pesanan, dan stok dalam satu tampilan modern.') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white">
                    🧾 {{ __('Kelola Pesanan') }}
                </a>
                <a href="{{ route('admin.inventory.low-stock') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white">
                    📦 {{ __('Cek Stok') }}
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-4">
        <x-ui.card class="relative overflow-hidden">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-rose-500/10 blur-2xl"></div>
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">{{ __('Revenue') }}</p>
            <p class="mt-4 text-2xl font-semibold text-slate-900 dark:text-white">Rp {{ number_format($metrics['revenue'] ?? 0, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Total pendapatan dari pesanan berbayar.') }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">{{ __('Total Pesanan') }}</p>
            <p class="mt-4 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['orders'] ?? 0) }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Semua pesanan yang masuk ke sistem.') }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">{{ __('Pesanan Dibayar') }}</p>
            <p class="mt-4 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['paid_orders'] ?? 0) }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Pesanan yang sudah diproses pembayaran.') }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">{{ __('Stok Rendah') }}</p>
            <p class="mt-4 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['low_stock'] ?? 0) }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">{{ __('Produk dengan stok di bawah batas aman.') }}</p>
        </x-ui.card>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Revenue 12 Bulan') }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-300">{{ __('Pantau tren pendapatan per bulan.') }}</p>
                </div>
                <x-ui.badge variant="info">IDR</x-ui.badge>
            </div>
            <div class="mt-6 h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </x-ui.card>
        <x-ui.card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Top 5 Souvenir') }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-300">{{ __('Produk terlaris 30 hari terakhir.') }}</p>
                </div>
            </div>
            <div id="topSouvenirsList" class="mt-6 space-y-4 text-sm text-slate-500">
                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center dark:border-slate-700">{{ __('Memuat data...') }}</div>
            </div>
        </x-ui.card>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Pesanan 30 Hari') }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-300">{{ __('Frekuensi pesanan terbaru.') }}</p>
                </div>
            </div>
            <div class="mt-6 h-64">
                <canvas id="ordersChart"></canvas>
            </div>
        </x-ui.card>
        <x-ui.card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Stok Kritis') }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-300">{{ __('Segera restock produk berikut.') }}</p>
            <div class="mt-6 space-y-4">
                @forelse($lowStockItems as $item)
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $item->name }}</p>
                            <p class="text-xs text-slate-500">{{ __('Sisa') }} {{ $item->stock }}</p>
                        </div>
                        <x-ui.badge variant="warning">{{ __('Low') }}</x-ui.badge>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500 dark:border-slate-700">
                        {{ __('Semua stok aman.') }}
                    </div>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <div class="mt-8">
        <x-ui.card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Pesanan Terbaru') }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-300">{{ __('Pantau transaksi terbaru dari pelanggan.') }}</p>
                </div>
            </div>
            <div class="mt-6 overflow-x-auto">
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
                            <th class="pb-3">{{ __('Total') }}</th>
                            <th class="pb-3">{{ __('Pembayaran') }}</th>
                            <th class="pb-3">{{ __('Status') }}</th>
                            <th class="pb-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800">
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900 dark:text-white">#ORDER-{{ $order->id }}</td>
                                <td class="py-3">
                                    <div class="text-slate-900 dark:text-white">{{ $order->user?->username }}</div>
                                    <div class="text-xs text-slate-500">{{ $order->user?->email }}</div>
                                </td>
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
                                <td colspan="6" class="py-6 text-center text-sm text-slate-500">
                                    {{ __('Belum ada pesanan terbaru.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const chartEndpoint = @json(route('admin.dashboard.charts'));

            const formatIdr = (value) => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(value || 0);

            const initCharts = (payload) => {
                const revenueCanvas = document.getElementById('revenueChart');
                const ordersCanvas = document.getElementById('ordersChart');

                if (revenueCanvas) {
                    new Chart(revenueCanvas, {
                        type: 'line',
                        data: {
                            labels: payload.revenue.labels,
                            datasets: [{
                                label: 'Revenue',
                                data: payload.revenue.series,
                                borderColor: '#f43f5e',
                                backgroundColor: 'rgba(244, 63, 94, 0.15)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => formatIdr(context.parsed.y),
                                    },
                                },
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: { ticks: { callback: (value) => formatIdr(value) } },
                            },
                        },
                    });
                }

                if (ordersCanvas) {
                    new Chart(ordersCanvas, {
                        type: 'bar',
                        data: {
                            labels: payload.orders.labels,
                            datasets: [{
                                label: 'Orders',
                                data: payload.orders.series,
                                backgroundColor: 'rgba(59, 130, 246, 0.45)',
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: { ticks: { precision: 0 } },
                            },
                        },
                    });
                }

                const list = document.getElementById('topSouvenirsList');
                if (list) {
                    list.innerHTML = '';

                    if (!payload.topSouvenirs.length) {
                        const empty = document.createElement('div');
                        empty.className = 'rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500 dark:border-slate-700';
                        empty.textContent = '{{ __('Belum ada data penjualan.') }}';
                        list.appendChild(empty);
                        return;
                    }

                    payload.topSouvenirs.forEach((item, index) => {
                        const row = document.createElement('div');
                        row.className = 'flex items-center justify-between gap-4';

                        const info = document.createElement('div');
                        const name = document.createElement('p');
                        name.className = 'font-semibold text-slate-900 dark:text-white';
                        name.textContent = item.name;
                        const meta = document.createElement('p');
                        meta.className = 'text-xs text-slate-500';
                        meta.textContent = `#${index + 1} · ${item.total} {{ __('terjual') }}`;
                        info.appendChild(name);
                        info.appendChild(meta);

                        const badge = document.createElement('span');
                        badge.className = 'inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200';
                        badge.textContent = item.total;

                        row.appendChild(info);
                        row.appendChild(badge);
                        list.appendChild(row);
                    });
                }
            };

            fetch(chartEndpoint, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((response) => response.json())
                .then((payload) => initCharts(payload))
                .catch(() => {
                    const list = document.getElementById('topSouvenirsList');
                    if (list) {
                        list.innerHTML = '<div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500 dark:border-slate-700">{{ __('Gagal memuat data grafik.') }}</div>';
                    }
                });
        </script>
    @endpush
</x-admin-layout>
