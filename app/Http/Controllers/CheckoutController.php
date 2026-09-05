<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\Inventory\InventoryService;
use App\Services\Orders\OrderInventoryService;
use App\Services\Payments\PaymentPayload;
use App\Services\Payments\PaymentService;
use App\Support\CacheKeys;
use App\Support\CheckoutIdempotency;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    // FUNGSI 1: Memproses Keranjang Menjadi Order
    public function process(
        Request $request,
        PaymentService $paymentService,
        OrderInventoryService $orderInventory,
        InventoryService $inventory,
        CheckoutIdempotency $checkoutIdempotency
    ) {
        $userId = (int) Auth::id();
        $intent = $request->validate([
            'checkout_token' => ['required', 'uuid'],
            'payment_provider' => ['required', Rule::enum(PaymentProvider::class)],
        ]);
        $checkoutToken = (string) $intent['checkout_token'];
        $idempotencyKey = $checkoutIdempotency->hash($checkoutToken);
        $provider = PaymentProvider::from((string) $intent['payment_provider']);

        $existingOrder = $this->findIdempotentOrder($idempotencyKey);
        if ($existingOrder) {
            $this->assertIdempotentOrderOwner($existingOrder, $userId);

            return $this->redirectForExistingCheckout($existingOrder);
        }

        if (! $checkoutIdempotency->matches($checkoutToken)) {
            throw ValidationException::withMessages([
                'checkout_token' => __('Sesi checkout sudah tidak berlaku. Muat ulang keranjang dan coba lagi.'),
            ]);
        }

        $cart = Session::get('cart', []);
        [$cart, $sanitized] = $this->sanitizeCheckoutCart($cart);
        if ($sanitized) {
            Session::put('cart', $cart);
            $checkoutIdempotency->forget();

            return redirect()->route('cart.index')
                ->with('error', __('Keranjang diperbarui karena ada perubahan ketersediaan stok. Periksa kembali sebelum checkout.'));
        }

        if (empty($cart)) {
            $checkoutIdempotency->forget();

            return redirect()->route('shop.index')->with('error', __('Keranjang belanja kosong.'));
        }

        $validated = $request->validate([
            'shipping_address_id' => [
                'required',
                'integer',
                Rule::exists('user_addresses', 'id')
                    ->where(fn ($query) => $query->where('user_id', $userId)),
            ],
        ]);

        $shippingAddressId = (int) $validated['shipping_address_id'];

        try {
            // Mulai Simpan ke Database (Pakai Transaction Biar Aman)
            [$order, $payment, $created] = DB::transaction(function () use ($cart, $idempotencyKey, $inventory, $provider, $shippingAddressId, $userId) {
                $customer = User::query()
                    ->whereKey($userId)
                    ->lockForUpdate()
                    ->first();

                if (! $customer) {
                    throw new \RuntimeException(__('Akun pengguna tidak lagi tersedia.'));
                }

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
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $existingOrder = $this->findIdempotentOrder($idempotencyKey);
                if ($existingOrder) {
                    $this->assertIdempotentOrderOwner($existingOrder, $userId);
                    $existingPayment = $existingOrder->payment;
                    if (! $existingPayment) {
                        throw new \RuntimeException(__('Pembayaran tidak ditemukan.'));
                    }

                    return [$existingOrder, $existingPayment, false];
                }

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
                    'user_id' => $customer->getKey(),
                    'customer_snapshot' => $this->customerSnapshot($customer),
                    'checkout_idempotency_key' => $idempotencyKey,
                    'shipping_address_id' => $shippingAddress->id,
                    'shipping_address_snapshot' => $this->shippingAddressSnapshot($shippingAddress),
                    'total_price' => $total,
                    'status' => OrderStatus::Pending,
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

                    $inventory->adjust(
                        souvenirId: (int) $data['souvenir']->getKey(),
                        quantityDelta: -$data['qty'],
                        type: InventoryMovement::TYPE_ORDER_RESERVATION,
                        reference: 'order:'.$order->id.':reservation:souvenir:'.$data['souvenir']->getKey(),
                        orderId: (int) $order->getKey(),
                        actorId: $userId,
                    );
                }

                $providerRef = $provider === PaymentProvider::Midtrans
                    ? 'ORD-'.$order->id.'-'.Str::uuid()
                    : null;

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'provider' => $provider,
                    'provider_ref' => $providerRef,
                    'status' => PaymentStatus::Pending,
                    'amount' => $total,
                    'currency' => 'IDR',
                ]);

                return [$order->loadMissing('items', 'user'), $payment, true];
            });
        } catch (QueryException $exception) {
            $existingOrder = $this->findIdempotentOrder($idempotencyKey);
            if (! $existingOrder) {
                throw $exception;
            }

            $this->assertIdempotentOrderOwner($existingOrder, $userId);

            return $this->redirectForExistingCheckout($existingOrder);
        } catch (\RuntimeException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        if (! $created) {
            return $this->redirectForExistingCheckout($order);
        }

        CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);

        $gatewayCreated = false;

        try {
            $result = $paymentService->driver($provider)->createPayment($order, $payment);
            $gatewayCreated = true;

            $payload = $payment->payload_json ?? [];
            $payload['gateway'] = PaymentPayload::gateway($provider, $result);

            $payment->update([
                'provider_ref' => $result->providerRef,
                'payload_json' => $payload,
                'amount' => $result->amount ?? $payment->amount,
                'currency' => $result->currency ?? $payment->currency,
            ]);
        } catch (\Throwable) {
            if (! $gatewayCreated) {
                $this->compensateFailedCheckout($order, $payment, $orderInventory);
                CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);
            } else {
                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'payload_json' => [
                        'failure' => PaymentPayload::failure('gateway_persistence_failed'),
                    ],
                ]);
            }

            $checkoutIdempotency->forget();

            return redirect()->route('cart.index')
                ->with('error', __('Gagal membuat pembayaran. Silakan coba lagi.'));
        }

        // Kosongkan Keranjang
        Session::forget('cart');
        $checkoutIdempotency->forget();

        return redirect()->away($result->redirectUrl);
    }

    private function findIdempotentOrder(string $idempotencyKey): ?Order
    {
        return Order::query()
            ->with('payment')
            ->where('checkout_idempotency_key', $idempotencyKey)
            ->first();
    }

    private function assertIdempotentOrderOwner(Order $order, int $userId): void
    {
        if ((int) $order->user_id === $userId) {
            return;
        }

        throw ValidationException::withMessages([
            'checkout_token' => __('Token checkout tidak valid.'),
        ]);
    }

    private function redirectForExistingCheckout(Order $order): RedirectResponse
    {
        $payment = $order->payment;
        if ($payment) {
            $redirectUrl = $this->extractRedirectUrlFromPayment($payment);
            if ($payment->status === PaymentStatus::Pending && $redirectUrl !== null) {
                return redirect()->away($redirectUrl);
            }

            if ($payment->status === PaymentStatus::Paid) {
                return redirect()->route('orders.show', $order)
                    ->with('success', __('Pembayaran pesanan ini sudah diterima.'));
            }

            if ($payment->status === PaymentStatus::Pending) {
                return redirect()->route('orders.show', $order)
                    ->with('error', __('Pembayaran sebelumnya sedang diproses. Silakan cek kembali status pesanan.'));
            }
        }

        return redirect()->route('orders.show', $order)
            ->with('error', __('Permintaan checkout ini sudah diproses dan tidak akan dibuat ulang.'));
    }

    protected function compensateFailedCheckout(
        Order $order,
        Payment $payment,
        OrderInventoryService $orderInventory
    ): void {
        DB::transaction(function () use ($order, $payment, $orderInventory) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->first();
            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder || ! $lockedPayment) {
                return;
            }

            if ($lockedOrder->status === OrderStatus::Pending) {
                $orderInventory->restore($lockedOrder->id);
                $lockedOrder->update([
                    'status' => OrderStatus::Cancelled,
                ]);
            }

            $payload = $lockedPayment->payload_json ?? [];
            $payload['failure'] = PaymentPayload::failure('gateway_creation_failed');

            $lockedPayment->update([
                'status' => PaymentStatus::Failed,
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
            'payment_provider' => ['required', Rule::enum(PaymentProvider::class)],
        ]);

        $provider = PaymentProvider::from((string) $validated['payment_provider']);
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

            if ($lockedOrder->status === OrderStatus::Completed) {
                $retryOutcome = 'order_completed';

                return;
            }

            if ($lockedOrder->status === OrderStatus::Cancelled) {
                $retryOutcome = 'order_cancelled';

                return;
            }

            if ($lockedOrder->status !== OrderStatus::Pending) {
                $retryOutcome = 'order_not_retryable';

                return;
            }

            $pendingPayment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('status', PaymentStatus::Pending->value)
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
                ->where('status', PaymentStatus::Paid->value)
                ->exists();

            if ($paidPaymentExists) {
                $retryOutcome = 'already_paid';

                return;
            }

            $payment = Payment::create([
                'order_id' => $lockedOrder->id,
                'provider' => $provider,
                'provider_ref' => $provider === PaymentProvider::Midtrans
                    ? 'ORD-'.$lockedOrder->id.'-'.Str::uuid()
                    : null,
                'status' => PaymentStatus::Pending,
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
            $payload['gateway'] = PaymentPayload::gateway($provider, $result);

            $payment->update([
                'provider_ref' => $result->providerRef,
                'payload_json' => $payload,
                'amount' => $result->amount ?? $payment->amount,
                'currency' => $result->currency ?? $payment->currency,
            ]);
        } catch (\Throwable) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload_json' => [
                    'failure' => PaymentPayload::failure('gateway_creation_failed'),
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

    /** @return array{username: string, email: string} */
    private function customerSnapshot(User $customer): array
    {
        return [
            'username' => (string) $customer->username,
            'email' => (string) $customer->email,
        ];
    }
}
