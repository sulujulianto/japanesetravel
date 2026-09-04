<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\UserAddress;
use App\Services\Orders\OrderInventoryService;
use App\Services\Payments\PaymentService;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    // FUNGSI 1: Memproses Keranjang Menjadi Order
    public function process(
        Request $request,
        PaymentService $paymentService,
        OrderInventoryService $orderInventory
    ) {
        $cart = Session::get('cart', []);
        [$cart, $sanitized] = $this->sanitizeCheckoutCart($cart);
        if ($sanitized) {
            Session::put('cart', $cart);

            return redirect()->route('cart.index')
                ->with('error', __('Keranjang diperbarui karena ada perubahan ketersediaan stok. Periksa kembali sebelum checkout.'));
        }

        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', __('Keranjang belanja kosong.'));
        }

        $userId = (int) Auth::id();
        $validated = $request->validate([
            'payment_provider' => 'required|in:midtrans,paypal',
            'shipping_address_id' => [
                'required',
                'integer',
                Rule::exists('user_addresses', 'id')
                    ->where(fn ($query) => $query->where('user_id', $userId)),
            ],
        ]);

        $provider = $validated['payment_provider'];
        $shippingAddressId = (int) $validated['shipping_address_id'];

        try {
            // Mulai Simpan ke Database (Pakai Transaction Biar Aman)
            [$order, $payment] = DB::transaction(function () use ($cart, $provider, $shippingAddressId, $userId) {
                $shippingAddress = UserAddress::query()
                    ->where('user_id', $userId)
                    ->whereKey($shippingAddressId)
                    ->lockForUpdate()
                    ->first();

                if (! $shippingAddress) {
                    throw new \RuntimeException(__('Alamat pengiriman tidak valid. Pilih kembali alamat Anda.'));
                }

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
                    'user_id' => $userId,
                    'shipping_address_id' => $shippingAddress->id,
                    'shipping_address_snapshot' => $this->shippingAddressSnapshot($shippingAddress),
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
                    ? 'ORD-'.$order->id.'-'.Str::uuid()
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

        $gatewayCreated = false;

        try {
            $result = $paymentService->driver($provider)->createPayment($order, $payment);
            $gatewayCreated = true;

            $payload = $payment->payload_json ?? [];
            $payload['gateway'] = $result->payload;

            $payment->update([
                'provider_ref' => $result->providerRef,
                'payload_json' => $payload,
                'amount' => $result->amount ?? $payment->amount,
                'currency' => $result->currency ?? $payment->currency,
            ]);
        } catch (\Throwable $exception) {
            if (! $gatewayCreated) {
                $this->compensateFailedCheckout($order, $payment, $exception, $orderInventory);
                CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'payload_json' => [
                        'error' => $exception->getMessage(),
                    ],
                ]);
            }

            return redirect()->route('cart.index')
                ->with('error', __('Gagal membuat pembayaran. Silakan coba lagi.'));
        }

        // Kosongkan Keranjang
        Session::forget('cart');

        return redirect()->away($result->redirectUrl);
    }

    protected function compensateFailedCheckout(
        Order $order,
        Payment $payment,
        \Throwable $exception,
        OrderInventoryService $orderInventory
    ): void {
        DB::transaction(function () use ($order, $payment, $exception, $orderInventory) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->first();
            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder || ! $lockedPayment) {
                return;
            }

            if ($lockedOrder->status === 'pending') {
                $orderInventory->restore($lockedOrder->id);
                $lockedOrder->update([
                    'status' => 'cancelled',
                ]);
            }

            $payload = $lockedPayment->payload_json ?? [];
            $payload['error'] = $exception->getMessage();

            $lockedPayment->update([
                'status' => 'failed',
                'payload_json' => $payload,
            ]);
        });
    }

    /**
     * @param  array<int|string, int|string>  $cart
     * @return array{0: array<int, int>, 1: bool}
     */
    private function sanitizeCheckoutCart(array $cart): array
    {
        if (empty($cart)) {
            return [$cart, false];
        }

        $changed = false;
        $souvenirs = Souvenir::whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');
        $sanitized = [];

        foreach ($cart as $id => $qty) {
            $id = (int) $id;
            $souvenir = $souvenirs->get($id);
            if (! $souvenir || $souvenir->stock <= 0) {
                $changed = true;

                continue;
            }

            $normalizedQty = max(1, (int) $qty);
            $clampedQty = min($normalizedQty, (int) $souvenir->stock);

            if ($clampedQty !== (int) $qty) {
                $changed = true;
            }

            $sanitized[$id] = $clampedQty;
        }

        if (count($sanitized) !== count($cart)) {
            $changed = true;
        }

        return [$sanitized, $changed];
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

        $validated = $request->validate([
            'payment_provider' => 'required|in:midtrans,paypal',
        ]);

        $provider = $validated['payment_provider'];
        $gatewayOrder = null;
        $payment = null;
        $reuseRedirectUrl = null;
        $retryOutcome = 'create_new';

        DB::transaction(function () use ($order, $provider, &$gatewayOrder, &$payment, &$reuseRedirectUrl, &$retryOutcome) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                $retryOutcome = 'not_found';

                return;
            }

            if ($lockedOrder->status === 'completed') {
                $retryOutcome = 'order_completed';

                return;
            }

            if ($lockedOrder->status === 'cancelled') {
                $retryOutcome = 'order_cancelled';

                return;
            }

            if ($lockedOrder->status !== 'pending') {
                $retryOutcome = 'order_not_retryable';

                return;
            }

            $pendingPayment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('status', 'pending')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($pendingPayment) {
                $reuseRedirectUrl = $this->extractRedirectUrlFromPayment($pendingPayment);
                $retryOutcome = $reuseRedirectUrl !== null
                    ? 'reuse_pending'
                    : 'pending_without_redirect';

                return;
            }

            $paidPaymentExists = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('status', 'paid')
                ->exists();

            if ($paidPaymentExists) {
                $retryOutcome = 'already_paid';

                return;
            }

            $payment = Payment::create([
                'order_id' => $lockedOrder->id,
                'provider' => $provider,
                'provider_ref' => $provider === 'midtrans'
                    ? 'ORD-'.$lockedOrder->id.'-'.Str::uuid()
                    : null,
                'status' => 'pending',
                'amount' => $lockedOrder->total_price,
                'currency' => 'IDR',
            ]);

            $gatewayOrder = $lockedOrder->loadMissing('items', 'user');
            $retryOutcome = 'created_new_payment';
        });

        if ($retryOutcome === 'order_completed') {
            return redirect()->route('orders.show', $order)->with('error', __('Pesanan sudah selesai dan tidak dapat dibayar ulang.'));
        }

        if ($retryOutcome === 'order_cancelled') {
            return redirect()->route('orders.show', $order)->with('error', __('Pesanan sudah dibatalkan dan tidak dapat dibayar ulang.'));
        }

        if ($retryOutcome === 'order_not_retryable' || $retryOutcome === 'not_found') {
            return redirect()->route('orders.show', $order)->with('error', __('Pesanan tidak dapat dibayar ulang.'));
        }

        if ($retryOutcome === 'reuse_pending' && $reuseRedirectUrl !== null) {
            return redirect()->away($reuseRedirectUrl);
        }

        if ($retryOutcome === 'pending_without_redirect') {
            return redirect()->route('orders.show', $order)->with('error', __('Pembayaran sebelumnya sedang diproses. Silakan cek kembali status pesanan.'));
        }

        if ($retryOutcome === 'already_paid') {
            return redirect()->route('orders.show', $order)->with('success', __('Pembayaran pesanan ini sudah diterima.'));
        }

        if ($retryOutcome !== 'created_new_payment' || ! $payment) {
            return redirect()->route('orders.show', $order)->with('error', __('Pesanan tidak dapat dibayar ulang.'));
        }

        try {
            $result = $paymentService->driver($provider)->createPayment($gatewayOrder ?? $order->loadMissing('items', 'user'), $payment);

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

    private function extractRedirectUrlFromPayment(Payment $payment): ?string
    {
        $payload = $payment->payload_json ?? [];
        $gatewayPayload = is_array($payload['gateway'] ?? null) ? $payload['gateway'] : [];

        $directRedirectUrl = $gatewayPayload['redirect_url']
            ?? $gatewayPayload['payment_url']
            ?? $payload['redirect_url']
            ?? $payload['payment_url']
            ?? null;

        if (is_string($directRedirectUrl) && $directRedirectUrl !== '') {
            return $directRedirectUrl;
        }

        $links = $gatewayPayload['links'] ?? [];
        if (is_array($links)) {
            foreach ($links as $link) {
                if (! is_array($link)) {
                    continue;
                }

                if (($link['rel'] ?? null) !== 'approve') {
                    continue;
                }

                $href = $link['href'] ?? null;
                if (is_string($href) && $href !== '') {
                    return $href;
                }
            }
        }

        return null;
    }

    /**
     * @return array{
     *     label: string,
     *     recipient_name: string,
     *     recipient_phone: string,
     *     address_line_1: string,
     *     address_line_2: string|null,
     *     city: string,
     *     province: string,
     *     postal_code: string,
     *     country_code: string
     * }
     */
    private function shippingAddressSnapshot(UserAddress $address): array
    {
        return [
            'label' => (string) $address->label,
            'recipient_name' => (string) $address->recipient_name,
            'recipient_phone' => (string) $address->recipient_phone,
            'address_line_1' => (string) $address->address_line_1,
            'address_line_2' => $address->address_line_2 === null ? null : (string) $address->address_line_2,
            'city' => (string) $address->city,
            'province' => (string) $address->province,
            'postal_code' => (string) $address->postal_code,
            'country_code' => (string) $address->country_code,
        ];
    }
}
