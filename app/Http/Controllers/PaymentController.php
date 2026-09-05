<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\Orders\OrderInventoryService;
use App\Services\Payments\Drivers\PayPalCheckoutDriver;
use App\Services\Payments\PaymentAmount;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PaymentWebhookData;
use App\Support\CacheKeys;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly OrderInventoryService $orderInventory) {}

    public function midtransWebhook(Request $request, PaymentService $paymentService): Response
    {
        return $this->handleWebhook($request, $paymentService, 'midtrans');
    }

    public function paypalWebhook(Request $request, PaymentService $paymentService): Response
    {
        return $this->handleWebhook($request, $paymentService, 'paypal');
    }

    public function paypalReturn(Request $request, PaymentService $paymentService): RedirectResponse
    {
        $providerRef = (string) $request->query('token', '');

        if ($providerRef === '') {
            return redirect()->route('orders.index')->with('error', __('Token pembayaran tidak valid.'));
        }

        $payment = Payment::where('provider', 'paypal')->where('provider_ref', $providerRef)->first();
        if (! $payment) {
            return redirect()->route('orders.index')->with('error', __('Pembayaran tidak ditemukan.'));
        }

        $driver = $paymentService->driver('paypal');
        if (! $driver instanceof PayPalCheckoutDriver) {
            return redirect()->route('orders.index')->with('error', __('Provider pembayaran tidak valid.'));
        }

        try {
            $response = $driver->captureOrder($providerRef);
        } catch (\Throwable $exception) {
            Log::warning('PayPal capture failed on return callback.', [
                'payment_id' => $payment->id,
                'provider_ref' => $providerRef,
                'exception' => $exception->getMessage(),
            ]);

            DB::transaction(function () use ($payment, $exception): void {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedPayment || ! $lockedPayment->canTransitionTo('failed')) {
                    return;
                }

                $payload = $lockedPayment->payload_json ?? [];
                $payload['capture_error'] = $exception->getMessage();

                $lockedPayment->update([
                    'status' => 'failed',
                    'payload_json' => $payload,
                ]);
            });

            return $this->redirectToOrderError(
                $payment,
                __('Gagal memproses pembayaran PayPal. Silakan coba lagi atau hubungi admin.')
            );
        }

        $status = $response['status'] ?? '';

        $message = __('Pembayaran berhasil diproses.');

        if ($status === 'COMPLETED') {
            $capture = $driver->parseCapture($response);
            $outcome = $this->applyPayPalCapture($payment, $capture);

            if ($outcome === 'integrity_failed') {
                Log::warning('PayPal capture failed payment integrity validation.', [
                    'payment_id' => $payment->id,
                    'expected_provider_ref' => $payment->provider_ref,
                    'received_provider_ref' => $capture->providerRef,
                    'expected_amount' => (string) $payment->amount,
                    'received_amount' => $capture->amount,
                    'expected_currency' => $payment->currency,
                    'received_currency' => $capture->currency,
                ]);

                return $this->redirectToOrderError(
                    $payment,
                    __('Data pembayaran PayPal tidak sesuai dengan tagihan. Hubungi admin untuk pemeriksaan.')
                );
            }

            if (! in_array($outcome, ['applied', 'already_paid'], true)) {
                return $this->redirectToOrderError(
                    $payment,
                    __('Gagal memproses pembayaran PayPal. Silakan coba lagi atau hubungi admin.')
                );
            }
        } elseif ($status !== 'COMPLETED') {
            $message = __('Pembayaran sedang diproses.');
        }

        return $this->redirectToOrder($payment, $message);
    }

    public function paypalCancel(Request $request): RedirectResponse
    {
        return redirect()->route('orders.index')->with('error', __('Pembayaran dibatalkan.'));
    }

    protected function handleWebhook(Request $request, PaymentService $paymentService, string $provider): Response
    {
        $driver = $paymentService->driver($provider);

        if (! $driver->verifyWebhook($request)) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $data = $driver->parseWebhook($request);

        if ($data->providerRef === '') {
            return response()->json(['message' => 'Invalid provider reference.'], 400);
        }

        $payment = Payment::where('provider', $provider)
            ->where('provider_ref', $data->providerRef)
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        $this->applyWebhookUpdate($payment, $data, $provider);

        return response()->json(['message' => 'OK']);
    }

    protected function applyWebhookUpdate(Payment $payment, PaymentWebhookData $data, string $provider): void
    {
        /** @var array<string, int|string>|null $integrityFailure */
        $integrityFailure = null;

        $stockRestored = DB::transaction(function () use ($payment, $data, $provider, &$integrityFailure): bool {
            $order = Order::query()
                ->whereKey($payment->order_id)
                ->lockForUpdate()
                ->first();
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->first();

            if ($data->eventId !== '') {
                $inserted = PaymentWebhookEvent::query()->insertOrIgnore([
                    [
                        'payment_id' => $payment->id,
                        'provider' => $provider,
                        'event_id' => $data->eventId,
                        'status' => $data->status,
                        'payload_json' => json_encode($data->payload, JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                if ($inserted === 0) {
                    return false;
                }
            }

            if (! $lockedPayment || $data->status === 'ignored' || ! $lockedPayment->canTransitionTo($data->status)) {
                return false;
            }

            if (
                $data->status === 'paid'
                && ! PaymentAmount::matches(
                    (string) $lockedPayment->amount,
                    (string) $lockedPayment->currency,
                    $data->amount,
                    $data->currency,
                )
            ) {
                $integrityFailure = [
                    'payment_id' => $lockedPayment->id,
                    'expected_amount' => (string) $lockedPayment->amount,
                    'received_amount' => $data->amount,
                    'expected_currency' => (string) $lockedPayment->currency,
                    'received_currency' => $data->currency,
                ];

                return false;
            }

            $payload = $lockedPayment->payload_json ?? [];
            $payload['webhook'] = $data->payload;

            $lockedPayment->status = $data->status;
            $lockedPayment->payload_json = $payload;

            if ($data->status === 'paid') {
                $lockedPayment->paid_at = now();
            }

            $lockedPayment->save();

            if ($data->status === 'paid') {
                if ($order?->status === 'pending') {
                    $order->update(['status' => 'processing']);
                }

                return false;
            }

            if (
                $order?->status === 'pending'
                && in_array($data->status, ['failed', 'expired', 'refunded'], true)
            ) {
                $restored = $this->orderInventory->restore($order->id);
                $order->update(['status' => 'cancelled']);

                return $restored;
            }

            return false;
        });

        if ($stockRestored) {
            CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);
        }

        if ($integrityFailure !== null) {
            Log::warning('Payment webhook failed amount or currency validation.', $integrityFailure + [
                'provider' => $provider,
                'event_id' => $data->eventId,
            ]);
        }
    }

    protected function applyPayPalCapture(Payment $payment, PaymentWebhookData $capture): string
    {
        return DB::transaction(function () use ($payment, $capture): string {
            $order = Order::query()
                ->whereKey($payment->order_id)
                ->lockForUpdate()
                ->first();
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedPayment) {
                return 'missing';
            }

            $payload = $lockedPayment->payload_json ?? [];
            $payload['capture'] = $capture->payload;

            $valid = $capture->status === 'paid'
                && hash_equals((string) $lockedPayment->provider_ref, $capture->providerRef)
                && PaymentAmount::matches(
                    (string) $lockedPayment->amount,
                    (string) $lockedPayment->currency,
                    $capture->amount,
                    $capture->currency,
                );

            if (! $valid) {
                $payload['capture_integrity_error'] = [
                    'expected_provider_ref' => (string) $lockedPayment->provider_ref,
                    'received_provider_ref' => $capture->providerRef,
                    'expected_amount' => (string) $lockedPayment->amount,
                    'received_amount' => $capture->amount,
                    'expected_currency' => (string) $lockedPayment->currency,
                    'received_currency' => $capture->currency,
                ];

                $lockedPayment->update(['payload_json' => $payload]);

                return 'integrity_failed';
            }

            unset($payload['capture_integrity_error']);

            if ($lockedPayment->status === 'paid') {
                $lockedPayment->update(['payload_json' => $payload]);

                return 'already_paid';
            }

            if (! $lockedPayment->canTransitionTo('paid')) {
                return 'transition_rejected';
            }

            $lockedPayment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payload_json' => $payload,
            ]);

            if ($order?->status === 'pending') {
                $order->update(['status' => 'processing']);
            }

            return 'applied';
        });
    }

    protected function redirectToOrder(Payment $payment, string $message): RedirectResponse
    {
        if (Auth::check() && Auth::id() === $payment->order->user_id) {
            return redirect()->route('orders.show', $payment->order)->with('success', $message);
        }

        return redirect()->route('orders.index')->with('success', $message);
    }

    protected function redirectToOrderError(Payment $payment, string $message): RedirectResponse
    {
        $order = $payment->order()->first();
        if ($order && Auth::check() && Auth::id() === $order->user_id) {
            return redirect()->route('orders.show', $order)->with('error', $message);
        }

        return redirect()->route('orders.index')->with('error', $message);
    }
}
