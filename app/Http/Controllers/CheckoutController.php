<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Services\Payments\PaymentService;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // FUNGSI 1: Memproses Keranjang Menjadi Order
    public function process(Request $request, PaymentService $paymentService)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', __('Keranjang belanja kosong.'));
        }

        $validated = $request->validate([
            'payment_provider' => 'required|in:midtrans,paypal',
        ]);

        $provider = $validated['payment_provider'];

        try {
            // Mulai Simpan ke Database (Pakai Transaction Biar Aman)
            [$order, $payment] = DB::transaction(function () use ($cart, $provider) {
                // Ambil detail barang dari database dengan lock untuk mencegah oversell
                $souvenirs = Souvenir::whereIn('id', array_keys($cart))
                    ->lockForUpdate()
                    ->get();

                if ($souvenirs->count() !== count($cart)) {
                    throw new \RuntimeException(__('Sebagian barang sudah tidak tersedia.'));
                }

                $total = 0;
                $itemsToProcess = [];

                // Cek stok ulang di dalam transaksi
                foreach ($souvenirs as $item) {
                    $qty = (int) ($cart[$item->id] ?? 0);

                    if ($qty < 1) {
                        throw new \RuntimeException(__('Jumlah barang tidak valid.'));
                    }

                    if ($item->stock < $qty) {
                        throw new \RuntimeException(
                            __('Stok :name kurang (Sisa: :stock). Kurangi jumlah pembelian.', [
                                'name' => $item->name,
                                'stock' => $item->stock,
                            ])
                        );
                    }

                    $total += $item->price * $qty;
                    $itemsToProcess[] = [
                        'souvenir' => $item,
                        'qty' => $qty,
                        'price' => $item->price,
                    ];
                }

                // 1. Buat Nota Utama
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'total_price' => $total,
                    'status' => 'pending',
                    'note' => 'Pesanan Baru',
                ]);

                // 2. Masukkan Rincian Barang & Kurangi Stok
                foreach ($itemsToProcess as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'souvenir_id' => $data['souvenir']->id,
                        'quantity' => $data['qty'],
                        'price' => $data['price'],
                        'product_name' => $data['souvenir']->name,
                        'product_price' => $data['price'],
                        'product_image' => $data['souvenir']->image,
                    ]);

                    $data['souvenir']->decrement('stock', $data['qty']);
                }

                $providerRef = $provider === 'midtrans'
                    ? 'ORD-' . $order->id . '-' . Str::uuid()
                    : null;

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'provider' => $provider,
                    'provider_ref' => $providerRef,
                    'status' => 'pending',
                    'amount' => $total,
                    'currency' => 'IDR',
                ]);

                return [$order->loadMissing('items', 'user'), $payment];
            });
        } catch (\RuntimeException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        try {
            $result = $paymentService->driver($provider)->createPayment($order, $payment);

            $payload = $payment->payload_json ?? [];
            $payload['gateway'] = $result->payload;

            $payment->update([
                'provider_ref' => $result->providerRef,
                'payload_json' => $payload,
                'amount' => $result->amount ?? $payment->amount,
                'currency' => $result->currency ?? $payment->currency,
            ]);
        } catch (\Throwable $exception) {
            $payment->update([
                'status' => 'failed',
                'payload_json' => [
                    'error' => $exception->getMessage(),
                ],
            ]);

            return redirect()->route('cart.index')
                ->with('error', __('Gagal membuat pembayaran. Silakan coba lagi.'));
        }

        // Kosongkan Keranjang
        Session::forget('cart');

        return redirect()->away($result->redirectUrl);
    }

    // FUNGSI 2: Melihat Riwayat Pesanan
    public function index()
    {
        // Ambil pesanan milik user yang sedang login
        $orders = Order::where('user_id', Auth::id())
                       ->with(['items.product', 'payment'])
                       ->latest()
                       ->paginate(10)
                       ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'payment']);

        return view('orders.show', compact('order'));
    }

    public function pay(Request $request, Order $order, PaymentService $paymentService)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (! in_array($order->status, ['pending'], true)) {
            return redirect()->route('orders.show', $order)->with('error', __('Pesanan tidak dapat dibayar ulang.'));
        }

        $validated = $request->validate([
            'payment_provider' => 'required|in:midtrans,paypal',
        ]);

        $provider = $validated['payment_provider'];

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'provider_ref' => $provider === 'midtrans'
                ? 'ORD-' . $order->id . '-' . Str::uuid()
                : null,
            'status' => 'pending',
            'amount' => $order->total_price,
            'currency' => 'IDR',
        ]);

        try {
            $result = $paymentService->driver($provider)->createPayment($order->loadMissing('items', 'user'), $payment);

            $payload = $payment->payload_json ?? [];
            $payload['gateway'] = $result->payload;

            $payment->update([
                'provider_ref' => $result->providerRef,
                'payload_json' => $payload,
                'amount' => $result->amount ?? $payment->amount,
                'currency' => $result->currency ?? $payment->currency,
            ]);
        } catch (\Throwable $exception) {
            $payment->update([
                'status' => 'failed',
                'payload_json' => [
                    'error' => $exception->getMessage(),
                ],
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', __('Gagal membuat pembayaran. Silakan coba lagi.'));
        }

        return redirect()->away($result->redirectUrl);
    }
}
