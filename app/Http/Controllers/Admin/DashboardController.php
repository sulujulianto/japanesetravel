<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Souvenir;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = Cache::remember('admin:dashboard:metrics', now()->addSeconds(60), function () {
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
            ->get();

        $lowStockItems = Souvenir::where('stock', '<=', 5)
            ->orderBy('stock')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('metrics', 'recentOrders', 'lowStockItems'));
    }

    public function charts(Request $request): JsonResponse
    {
        $data = Cache::remember('admin:dashboard:charts', now()->addSeconds(90), function () {
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
                $months[] = $cursor->format('M Y');
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
                $days[] = $cursor->format('d M');
                $orderSeries[] = (int) ($orders[$key] ?? 0);
                $cursor->addDay();
            }

            $topSouvenirs = OrderItem::query()
                ->selectRaw('COALESCE(product_name, "Souvenir") as name, SUM(quantity) as total')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereIn('orders.status', $paidStatuses)
                ->groupBy('name')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'name' => $row->name,
                    'total' => (int) $row->total,
                ]);

            return [
                'revenue' => [
                    'labels' => $months,
                    'series' => $revenueSeries,
                ],
                'orders' => [
                    'labels' => $days,
                    'series' => $orderSeries,
                ],
                'topSouvenirs' => $topSouvenirs,
            ];
        });

        return response()->json($data);
    }
}
