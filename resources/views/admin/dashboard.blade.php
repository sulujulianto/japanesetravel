<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-5 rounded-2xl border border-[#E7E3DC] bg-white p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between dark:border-[#2A333D] dark:bg-[#161B22]">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Ringkasan Operasional') }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED] sm:text-3xl">{{ __('Dashboard Admin') }}</h1>
                <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Pantau penjualan, pesanan, dan stok dari data operasional terbaru.') }}</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('admin.orders.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[#B33A3A] px-4 text-sm font-semibold text-white transition hover:bg-[#8F2E2E] dark:bg-[#D96B6B] dark:text-[#0E1116] dark:hover:bg-[#E18484]">
                    {{ __('Kelola Pesanan') }}
                </a>
                <a href="{{ route('admin.inventory.low-stock') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#E7E3DC] px-4 text-sm font-semibold text-[#374151] transition hover:border-[#B33A3A] hover:text-[#8F2E2E] dark:border-[#2A333D] dark:text-[#D8DEE8] dark:hover:border-[#D96B6B] dark:hover:text-[#D96B6B]">
                    {{ __('Cek Stok') }}
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @php
        $dashboardCard = 'rounded-2xl border border-[#E7E3DC] bg-white p-5 dark:border-[#2A333D] dark:bg-[#161B22] sm:p-6';
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

    <section aria-labelledby="metrics-heading">
        <div class="mb-4">
            <h2 id="metrics-heading" class="text-base font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Metrik Utama') }}</h2>
            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Snapshot terbaru untuk penjualan, pesanan, dan persediaan.') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="{{ $dashboardCard }}">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Revenue') }}</p>
                <p class="mt-3 break-words text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($metrics['revenue'] ?? 0, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Total pendapatan dari pesanan berbayar.') }}</p>
            </article>
            <article class="{{ $dashboardCard }}">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Total Pesanan') }}</p>
                <p class="mt-3 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED]">{{ number_format($metrics['orders'] ?? 0) }}</p>
                <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Semua pesanan yang masuk ke sistem.') }}</p>
            </article>
            <article class="{{ $dashboardCard }}">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Pesanan Dibayar') }}</p>
                <p class="mt-3 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED]">{{ number_format($metrics['paid_orders'] ?? 0) }}</p>
                <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Pesanan yang sudah diproses pembayaran.') }}</p>
            </article>
            <article class="{{ $dashboardCard }}">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#667085] dark:text-[#AEB8C7]">{{ __('Stok Rendah') }}</p>
                <p class="mt-3 text-2xl font-semibold tracking-tight text-[#1F2937] dark:text-[#F4F1ED]">{{ number_format($metrics['low_stock'] ?? 0) }}</p>
                <p class="mt-2 text-sm leading-6 text-[#526071] dark:text-[#AEB8C7]">{{ __('Produk dengan stok di bawah batas aman.') }}</p>
            </article>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3" aria-label="{{ __('Analitik penjualan') }}">
        <article class="{{ $dashboardCard }} min-w-0 xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Revenue 12 Bulan') }}</h2>
                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Tren pendapatan bulanan dari pesanan berbayar.') }}</p>
                </div>
                <x-ui.badge variant="default">IDR</x-ui.badge>
            </div>
            <div class="mt-6 overflow-x-auto pb-1">
                <div class="h-72 min-w-[36rem] sm:min-w-0">
                    <canvas id="revenueChart"></canvas>
                    <div id="revenueChartEmpty" class="hidden h-full items-center justify-center rounded-xl border border-dashed border-[#E7E3DC] px-4 text-center text-sm text-[#526071] dark:border-[#2A333D] dark:text-[#AEB8C7]">
                        {{ __('Belum ada data revenue untuk periode ini.') }}
                    </div>
                </div>
            </div>
        </article>

        <article class="{{ $dashboardCard }}">
            <h2 class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Top 5 Souvenir') }}</h2>
            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Produk terlaris dari pesanan berbayar 30 hari terakhir.') }}</p>
            <div id="topSouvenirsList" class="mt-5 space-y-2">
                <div class="rounded-xl border border-dashed border-[#E7E3DC] p-4 text-center text-sm text-[#526071] dark:border-[#2A333D] dark:text-[#AEB8C7]">{{ __('Memuat data...') }}</div>
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3" aria-label="{{ __('Aktivitas pesanan dan stok') }}">
        <article class="{{ $dashboardCard }} min-w-0 xl:col-span-2">
            <h2 class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Pesanan 30 Hari') }}</h2>
            <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Frekuensi pesanan harian selama 30 hari terakhir.') }}</p>
            <div class="mt-6 overflow-x-auto pb-1">
                <div class="h-72 min-w-[44rem] sm:min-w-0">
                    <canvas id="ordersChart"></canvas>
                    <div id="ordersChartEmpty" class="hidden h-full items-center justify-center rounded-xl border border-dashed border-[#E7E3DC] px-4 text-center text-sm text-[#526071] dark:border-[#2A333D] dark:text-[#AEB8C7]">
                        {{ __('Belum ada pesanan untuk periode ini.') }}
                    </div>
                </div>
            </div>
        </article>

        <article class="{{ $dashboardCard }}">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Stok Kritis') }}</h2>
                    <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Produk yang perlu segera diperiksa atau direstock.') }}</p>
                </div>
                <a href="{{ route('admin.inventory.low-stock') }}" class="shrink-0 text-xs font-semibold text-[#B33A3A] transition hover:text-[#8F2E2E] dark:text-[#D96B6B] dark:hover:text-[#E18484]">{{ __('Lihat') }}</a>
            </div>
            <div class="mt-5 space-y-2">
                @forelse($lowStockItems as $item)
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] px-4 py-3 dark:border-[#2A333D] dark:bg-[#0E1116]">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ $item->name }}</p>
                            <p class="mt-1 text-xs text-[#526071] dark:text-[#AEB8C7]">{{ __('Sisa stok') }}: {{ $item->stock }}</p>
                        </div>
                        <x-ui.badge variant="warning">{{ $item->stock }}</x-ui.badge>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-[#E7E3DC] p-5 text-center dark:border-[#2A333D]">
                        <p class="text-sm font-semibold text-[#2F5D50] dark:text-[#8AB7A4]">{{ __('Semua stok aman.') }}</p>
                        <p class="mt-1 text-xs text-[#526071] dark:text-[#AEB8C7]">{{ __('Tidak ada produk di bawah batas stok saat ini.') }}</p>
                    </div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="{{ $dashboardCard }} mt-6" aria-labelledby="recent-orders-heading">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="recent-orders-heading" class="text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">{{ __('Pesanan Terbaru') }}</h2>
                <p class="mt-1 text-sm text-[#526071] dark:text-[#AEB8C7]">{{ __('Transaksi terbaru yang memerlukan pemantauan operasional.') }}</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-[#B33A3A] transition hover:text-[#8F2E2E] dark:text-[#D96B6B] dark:hover:text-[#E18484]">{{ __('Lihat semua pesanan') }}</a>
        </div>

        <div class="mt-5 hidden overflow-x-auto md:block">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#E7E3DC] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[#667085] dark:border-[#2A333D] dark:text-[#AEB8C7]">
                        <th class="px-2 pb-3">{{ __('Order') }}</th>
                        <th class="px-2 pb-3">{{ __('Pelanggan') }}</th>
                        <th class="px-2 pb-3">{{ __('Total') }}</th>
                        <th class="px-2 pb-3">{{ __('Pembayaran') }}</th>
                        <th class="px-2 pb-3">{{ __('Status') }}</th>
                        <th class="px-2 pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E3DC] dark:divide-[#2A333D]">
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="whitespace-nowrap px-2 py-4 font-semibold text-[#1F2937] dark:text-[#F4F1ED]">#ORDER-{{ $order->id }}</td>
                            <td class="px-2 py-4">
                                <div class="font-medium text-[#374151] dark:text-[#D8DEE8]">{{ $order->user?->username }}</div>
                                <div class="mt-1 text-xs text-[#526071] dark:text-[#AEB8C7]">{{ $order->user?->email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-2 py-4 font-semibold text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td class="px-2 py-4">
                                @if($order->payment)
                                    <x-ui.badge variant="{{ $paymentVariants[$order->payment->status] ?? 'default' }}">
                                        {{ strtoupper($order->payment->provider) }} · {{ __(strtoupper($order->payment->status)) }}
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="default">{{ __('Belum ada') }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-2 py-4">
                                <x-ui.badge variant="{{ $statusVariants[$order->status] ?? 'default' }}">
                                    {{ __(strtoupper($order->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-2 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-semibold text-[#B33A3A] transition hover:text-[#8F2E2E] dark:text-[#D96B6B] dark:hover:text-[#E18484]">{{ __('Detail') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-[#526071] dark:text-[#AEB8C7]">
                                {{ __('Belum ada pesanan terbaru.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5 space-y-3 md:hidden">
            @forelse($recentOrders as $order)
                <article class="rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] p-4 dark:border-[#2A333D] dark:bg-[#0E1116]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-[#1F2937] dark:text-[#F4F1ED]">#ORDER-{{ $order->id }}</p>
                            <p class="mt-1 truncate text-sm text-[#526071] dark:text-[#AEB8C7]">{{ $order->user?->username }} · {{ $order->user?->email }}</p>
                        </div>
                        <a href="{{ route('admin.orders.show', $order) }}" class="shrink-0 text-sm font-semibold text-[#B33A3A] dark:text-[#D96B6B]">{{ __('Detail') }}</a>
                    </div>
                    <p class="mt-4 text-lg font-semibold text-[#1F2937] dark:text-[#F4F1ED]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
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
                <div class="rounded-xl border border-dashed border-[#E7E3DC] p-5 text-center text-sm text-[#526071] dark:border-[#2A333D] dark:text-[#AEB8C7]">
                    {{ __('Belum ada pesanan terbaru.') }}
                </div>
            @endforelse
        </div>
    </section>

    @push('scripts')
        <script>
            const chartEndpoint = @json(route('admin.dashboard.charts'));

            const formatIdr = (value) => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(value || 0);

            const hasChartData = (series) => Array.isArray(series) && series.some((value) => Number(value) > 0);

            const showChartEmptyState = (canvas, emptyState) => {
                canvas.classList.add('hidden');
                emptyState.classList.remove('hidden');
                emptyState.classList.add('flex');
            };

            const initCharts = (payload) => {
                const revenueCanvas = document.getElementById('revenueChart');
                const revenueEmpty = document.getElementById('revenueChartEmpty');
                const ordersCanvas = document.getElementById('ordersChart');
                const ordersEmpty = document.getElementById('ordersChartEmpty');

                if (revenueCanvas && revenueEmpty) {
                    if (hasChartData(payload.revenue.series)) {
                        new Chart(revenueCanvas, {
                            type: 'line',
                            data: {
                                labels: payload.revenue.labels,
                                datasets: [{
                                    label: @json(__('Revenue')),
                                    data: payload.revenue.series,
                                    borderColor: '#B33A3A',
                                    backgroundColor: 'rgba(179, 58, 58, 0.08)',
                                    fill: true,
                                    tension: 0.32,
                                    pointRadius: 2,
                                    pointHoverRadius: 4,
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
                                    y: { beginAtZero: true, ticks: { callback: (value) => formatIdr(value) } },
                                },
                            },
                        });
                    } else {
                        showChartEmptyState(revenueCanvas, revenueEmpty);
                    }
                }

                if (ordersCanvas && ordersEmpty) {
                    if (hasChartData(payload.orders.series)) {
                        new Chart(ordersCanvas, {
                            type: 'bar',
                            data: {
                                labels: payload.orders.labels,
                                datasets: [{
                                    label: @json(__('Orders')),
                                    data: payload.orders.series,
                                    backgroundColor: 'rgba(47, 93, 80, 0.72)',
                                    borderRadius: 5,
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
                                    y: { beginAtZero: true, ticks: { precision: 0 } },
                                },
                            },
                        });
                    } else {
                        showChartEmptyState(ordersCanvas, ordersEmpty);
                    }
                }

                const list = document.getElementById('topSouvenirsList');
                if (list) {
                    list.innerHTML = '';

                    if (!payload.topSouvenirs.length) {
                        const empty = document.createElement('div');
                        empty.className = 'rounded-xl border border-dashed border-[#E7E3DC] p-5 text-center text-sm text-[#526071] dark:border-[#2A333D] dark:text-[#AEB8C7]';
                        empty.textContent = @json(__('Belum ada data penjualan.'));
                        list.appendChild(empty);
                        return;
                    }

                    payload.topSouvenirs.forEach((item, index) => {
                        const row = document.createElement('div');
                        row.className = 'flex items-center gap-3 rounded-xl border border-[#E7E3DC] bg-[#FAF8F3] px-3 py-3 dark:border-[#2A333D] dark:bg-[#0E1116]';

                        const rank = document.createElement('span');
                        rank.className = 'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-xs font-semibold text-[#8F2E2E] dark:bg-[#161B22] dark:text-[#D96B6B]';
                        rank.textContent = index + 1;

                        const info = document.createElement('div');
                        info.className = 'min-w-0 flex-1';
                        const name = document.createElement('p');
                        name.className = 'truncate text-sm font-semibold text-[#1F2937] dark:text-[#F4F1ED]';
                        name.textContent = item.name;
                        const meta = document.createElement('p');
                        meta.className = 'mt-1 text-xs text-[#526071] dark:text-[#AEB8C7]';
                        meta.textContent = `${item.total} ${@json(__('terjual'))}`;
                        info.appendChild(name);
                        info.appendChild(meta);

                        const total = document.createElement('span');
                        total.className = 'shrink-0 text-sm font-semibold text-[#2F5D50] dark:text-[#8AB7A4]';
                        total.textContent = item.total;

                        row.appendChild(rank);
                        row.appendChild(info);
                        row.appendChild(total);
                        list.appendChild(row);
                    });
                }
            };

            const showDashboardLoadError = () => {
                ['revenueChart', 'ordersChart'].forEach((canvasId) => {
                    document.getElementById(canvasId)?.classList.add('hidden');
                });

                ['revenueChartEmpty', 'ordersChartEmpty'].forEach((emptyId) => {
                    const emptyState = document.getElementById(emptyId);
                    if (emptyState) {
                        emptyState.textContent = @json(__('Gagal memuat data grafik.'));
                        emptyState.classList.remove('hidden');
                        emptyState.classList.add('flex');
                    }
                });

                const list = document.getElementById('topSouvenirsList');
                if (list) {
                    list.innerHTML = '';
                    const empty = document.createElement('div');
                    empty.className = 'rounded-xl border border-dashed border-[#E7E3DC] p-5 text-center text-sm text-[#526071] dark:border-[#2A333D] dark:text-[#AEB8C7]';
                    empty.textContent = @json(__('Gagal memuat data grafik.'));
                    list.appendChild(empty);
                }
            };

            document.addEventListener('DOMContentLoaded', () => {
                fetch(chartEndpoint, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Dashboard chart request failed.');
                        }

                        return response.json();
                    })
                    .then((payload) => initCharts(payload))
                    .catch(showDashboardLoadError);
            });
        </script>
    @endpush
</x-admin-layout>
