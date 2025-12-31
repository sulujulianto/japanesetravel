<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['user', 'payment']);

        $status = $request->string('status')->toString();
        if ($status !== '') {
            $query->where('status', $status);
        }

        $paymentStatus = $request->string('payment_status')->toString();
        if ($paymentStatus === 'unpaid') {
            $query->whereDoesntHave('payment');
        } elseif ($paymentStatus !== '') {
            $query->whereHas('payment', function ($paymentQuery) use ($paymentStatus) {
                $paymentQuery->where('status', $paymentStatus);
            });
        }

        $dateFrom = $request->string('date_from')->toString();
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        $dateTo = $request->string('date_to')->toString();
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $search = $request->string('q')->toString();
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                if (is_numeric($search)) {
                    $searchQuery->where('id', (int) $search);
                }

                $searchQuery->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%');
                });
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders', 'status', 'paymentStatus', 'dateFrom', 'dateTo', 'search'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'payments']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(UpdateOrderStatusRequest $request, Order $order)
    {
        $order->update([
            'status' => $request->input('status'),
            'admin_note' => $request->input('admin_note'),
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', __('Status pesanan berhasil diperbarui.'));
    }
}
