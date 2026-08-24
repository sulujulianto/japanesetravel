<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use App\Support\CacheKeys;
use App\Support\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /** @var array{revenue: float|int, orders: int, paid_orders: int, low_stock: int} $metrics */
        $metrics = Cache::remember('admin:dashboard:metrics', now()->addSeconds(60), function (): array {
            $paidStatuses = ['processing', 'completed'];

            return [
                'revenue' => Order::whereIn('status', $paidStatuses)->sum('total_price'),
                'orders' => Order::count(),
                'paid_orders' => Order::whereIn('status', $paidStatuses)->count(),
                'low_stock' => Souvenir::where('stock', '<=', 5)->count(),
            ];
        });

        $recentOrders = Order::with(['user', 'payment'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function (Order $order): array {
                $payment = $order->getRelation('payment');
                $user = $order->getRelation('user');

                return [
                    'id' => (int) $order->getKey(),
                    'customer' => [
                        'username' => $user instanceof User ? (string) $user->username : __('Pengguna dihapus'),
                        'email' => $user instanceof User ? (string) $user->email : '',
                    ],
                    'total' => Format::idr($order->total_price),
                    'payment' => ! $payment instanceof Payment ? null : [
                        'label' => strtoupper((string) $payment->provider).' · '.__(strtoupper((string) $payment->status)),
                        'status' => (string) $payment->status,
                    ],
                    'status' => [
                        'label' => __(strtoupper((string) $order->status)),
                        'value' => (string) $order->status,
                    ],
                    'url' => route('admin.orders.show', $order, absolute: false),
                ];
            });

        $lowStockItems = Souvenir::where('stock', '<=', 5)
            ->orderBy('stock')
            ->take(5)
            ->get()
            ->map(fn (Souvenir $souvenir): array => [
                'id' => (int) $souvenir->getKey(),
                'name' => (string) $souvenir->name,
                'stock' => (int) $souvenir->stock,
                'stockLabel' => Format::number($souvenir->stock),
            ]);

        return Inertia::render('Admin/Dashboard/Index', [
            'copy' => $this->copy(),
            'metrics' => [
                ['key' => 'revenue', 'label' => __('Revenue'), 'value' => Format::idr($metrics['revenue']), 'description' => __('Total pendapatan dari pesanan berbayar.')],
                ['key' => 'orders', 'label' => __('Total Pesanan'), 'value' => Format::number($metrics['orders']), 'description' => __('Semua pesanan yang masuk ke sistem.')],
                ['key' => 'paid-orders', 'label' => __('Pesanan Dibayar'), 'value' => Format::number($metrics['paid_orders']), 'description' => __('Pesanan yang sudah diproses pembayaran.')],
                ['key' => 'low-stock', 'label' => __('Stok Rendah'), 'value' => Format::number($metrics['low_stock']), 'description' => __('Produk dengan stok di bawah batas aman.')],
            ],
            'recentOrders' => $recentOrders,
            'lowStockItems' => $lowStockItems,
            'routes' => [
                'charts' => route('admin.dashboard.charts', absolute: false),
                'dashboard' => route('admin.dashboard', absolute: false),
                'home' => route('home', absolute: false),
                'localeEn' => route('lang.switch', ['locale' => 'en'], absolute: false),
                'localeId' => route('lang.switch', ['locale' => 'id'], absolute: false),
                'logout' => route('admin.logout', absolute: false),
                'lowStock' => route('admin.inventory.low-stock', absolute: false),
                'orders' => route('admin.orders.index', absolute: false),
                'places' => route('admin.places.index', absolute: false),
                'souvenirs' => route('admin.souvenirs.index', absolute: false),
            ],
        ]);
    }

    public function charts(): JsonResponse
    {
        $locale = Format::locale();
        $data = Cache::remember(CacheKeys::adminDashboardCharts($locale), now()->addSeconds(90), function () use ($locale) {
            $paidStatuses = ['processing', 'completed'];
            $startMonth = now()->subMonths(11)->startOfMonth();
            $endMonth = now()->endOfMonth();

            $revenue = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, SUM(total_price) as total")
                ->whereIn('status', $paidStatuses)
                ->whereBetween('created_at', [$startMonth, $endMonth])
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period');

            $months = [];
            $revenueSeries = [];
            $cursor = $startMonth->copy();
            while ($cursor <= $endMonth) {
                $key = $cursor->format('Y-m');
                $months[] = $cursor->locale($locale)->isoFormat('MMM YYYY');
                $revenueSeries[] = (float) ($revenue[$key] ?? 0);
                $cursor->addMonth();
            }

            $startDay = now()->subDays(29)->startOfDay();
            $endDay = now()->endOfDay();
            $orders = Order::selectRaw('DATE(created_at) as period, COUNT(*) as total')
                ->whereBetween('created_at', [$startDay, $endDay])
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period');

            $days = [];
            $orderSeries = [];
            $cursor = $startDay->copy();
            while ($cursor <= $endDay) {
                $key = $cursor->format('Y-m-d');
                $days[] = $cursor->locale($locale)->isoFormat('D MMM');
                $orderSeries[] = (int) ($orders[$key] ?? 0);
                $cursor->addDay();
            }

            $topSouvenirs = OrderItem::query()
                ->selectRaw('COALESCE(product_name, "Souvenir") as name, SUM(quantity) as total')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereIn('orders.status', $paidStatuses)
                ->whereBetween('orders.created_at', [$startDay, $endDay])
                ->groupBy('name')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($row) => ['name' => (string) $row->name, 'total' => (int) $row->total]);

            return [
                'revenue' => ['labels' => $months, 'series' => $revenueSeries],
                'orders' => ['labels' => $days, 'series' => $orderSeries],
                'topSouvenirs' => $topSouvenirs,
            ];
        });

        return response()->json($data);
    }

    /** @return array<string, string> */
    private function copy(): array
    {
        return [
            'allStockSafe' => __('Semua stok aman.'),
            'allStockSafeDescription' => __('Tidak ada produk di bawah batas stok saat ini.'),
            'chartsError' => __('Gagal memuat data grafik.'),
            'checkStock' => __('Cek Stok'),
            'closeMenu' => __('Tutup menu'),
            'criticalStockDescription' => __('Produk yang perlu segera diperiksa atau direstock.'),
            'criticalStockTitle' => __('Stok Kritis'),
            'customer' => __('Pelanggan'),
            'dashboard' => __('Dashboard'),
            'description' => __('Pantau penjualan, pesanan, dan stok dari data operasional terbaru.'),
            'detail' => __('Detail'),
            'eyebrow' => __('Ringkasan Operasional'),
            'loadingCharts' => __('Memuat data grafik...'),
            'logout' => __('Keluar'),
            'lowStock' => __('Stok Rendah'),
            'manageOrders' => __('Kelola Pesanan'),
            'menu' => __('Menu'),
            'metricsDescription' => __('Snapshot terbaru untuk penjualan, pesanan, dan persediaan.'),
            'metricsTitle' => __('Metrik Utama'),
            'navigation' => __('Navigasi admin'),
            'noOrdersChart' => __('Belum ada pesanan untuk periode ini.'),
            'noRecentOrders' => __('Belum ada pesanan terbaru.'),
            'noRevenueChart' => __('Belum ada data revenue untuk periode ini.'),
            'noSales' => __('Belum ada data penjualan.'),
            'order' => __('Order'),
            'orders' => __('Pesanan'),
            'ordersChartDescription' => __('Frekuensi pesanan harian selama 30 hari terakhir.'),
            'ordersChartTitle' => __('Pesanan 30 Hari'),
            'payment' => __('Pembayaran'),
            'places' => __('Destinasi'),
            'recentOrdersDescription' => __('Transaksi terbaru yang memerlukan pemantauan operasional.'),
            'recentOrdersTitle' => __('Pesanan Terbaru'),
            'remainingStock' => __('Sisa stok'),
            'revenueChartDescription' => __('Tren pendapatan bulanan dari pesanan berbayar.'),
            'revenueChartTitle' => __('Revenue 12 Bulan'),
            'sold' => __('terjual'),
            'souvenirs' => __('Souvenir'),
            'status' => __('Status'),
            'theme' => __('Tema'),
            'themeToggle' => __('Ganti tema'),
            'title' => __('Dashboard Admin'),
            'topSouvenirsDescription' => __('Produk terlaris dari pesanan berbayar 30 hari terakhir.'),
            'topSouvenirsTitle' => __('Top 5 Souvenir'),
            'total' => __('Total'),
            'view' => __('Lihat'),
            'viewAllOrders' => __('Lihat semua pesanan'),
            'viewSite' => __('Lihat Situs'),
            'workspace' => __('Admin Workspace'),
            'workspaceDescription' => __('Pantau operasional harian'),
        ];
    }
}
