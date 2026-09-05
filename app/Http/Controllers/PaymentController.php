<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\Orders\OrderInventoryService;
use App\Services\Payments\Drivers\PayPalCheckoutDriver;
use App\Services\Payments\PaymentAmount;
use App\Services\Payments\PaymentPayload;
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
        return $this->handleWebhook($request, $paymentService, PaymentProvider::Midtrans);
    }

    public function paypalWebhook(Request $request, PaymentService $paymentService): Response
    {
        return $this->handleWebhook($request, $paymentService, PaymentProvider::PayPal);
    }

    public function paypalReturn(Request $request, PaymentService $paymentService): RedirectResponse
    {
        $providerRef = (string) $request->query('token', '');

        if ($providerRef === '') {
            return redirect()->route('orders.index')->with('error', __('Token pembayaran tidak valid.'));
        }

        $payment = Payment::where('provider', PaymentProvider::PayPal->value)->where('provider_ref', $providerRef)->first();
        if (! $payment) {
            return redirect()->route('orders.index')->with('error', __('Pembayaran tidak ditemukan.'));
        }

        $driver = $paymentService->driver(PaymentProvider::PayPal);
        if (! $driver instanceof PayPalCheckoutDriver) {
            return redirect()->route('orders.index')->with('error', __('Provider pembayaran tidak valid.'));
        }

        try {
            $response = $driver->captureOrder($providerRef);
        } catch (\Throwable $exception) {
            Log::warning('PayPal capture failed on return callback.', [
                'payment_id' => $payment->id,
                'provider_ref' => $providerRef,
                'exception_class' => $exception::class,
            ]);

            DB::transaction(function () use ($payment): void {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedPayment || ! $lockedPayment->canTransitionTo(PaymentStatus::Failed)) {
                    return;
                }

                $payload = $lockedPayment->payload_json ?? [];
                $payload['failure'] = PaymentPayload::failure('paypal_capture_failed');
                unset($payload['gateway']);

                $lockedPayment->update([
                    'status' => PaymentStatus::Failed,
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

    protected function handleWebhook(Request $request, PaymentService $paymentService, PaymentProvider $provider): Response
    {
        $driver = $paymentService->driver($provider);

        if (! $driver->verifyWebhook($request)) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $data = $driver->parseWebhook($request);

        if ($data->providerRef === '') {
            return response()->json(['message' => 'Invalid provider reference.'], 400);
        }

        $payment = Payment::where('provider', $provider->value)
            ->where('provider_ref', $data->providerRef)
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        $this->applyWebhookUpdate($payment, $data, $provider);

        return response()->json(['message' => 'OK']);
    }

    protected function applyWebhookUpdate(Payment $payment, PaymentWebhookData $data, PaymentProvider $provider): void
    {
        /** @var array<string, int|string>|null $integrityFailure */
        $integrityFailure = null;
        $sanitizedPayload = PaymentPayload::webhook($provider, $data);

        $stockRestored = DB::transaction(function () use ($payment, $data, $provider, $sanitizedPayload, &$integrityFailure): bool {
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
                        'provider' => $provider->value,
                        'event_id' => $data->eventId,
                        'status' => $data->status->value,
                        'payload_json' => json_encode($sanitizedPayload, JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                if ($inserted === 0) {
                    return false;
                }
            }

            $nextStatus = $data->status->paymentStatus();
            if (! $lockedPayment || $nextStatus === null || ! $lockedPayment->canTransitionTo($nextStatus)) {
                return false;
            }

            if (
                $nextStatus === PaymentStatus::Paid
                && ! PaymentAmount::matches(
                    (string) $lockedPayment->amount,
                    (string) $lockedPayment->currency,
                    $data->amount,
                    $data->currency,
                )
            ) {
                $integrityFailure = ['payment_id' => $lockedPayment->id]
                    + PaymentPayload::integrityFailure($lockedPayment, $data);

                return false;
            }

            $payload = $lockedPayment->payload_json ?? [];
            $payload['webhook'] = $sanitizedPayload;

            if ($nextStatus !== PaymentStatus::Pending) {
                unset($payload['gateway']);
            }

            if ($nextStatus === PaymentStatus::Paid) {
                unset($payload['failure'], $payload['error'], $payload['capture_error']);
            }

            $lockedPayment->status = $nextStatus;
            $lockedPayment->payload_json = $payload;

            if ($nextStatus === PaymentStatus::Paid) {
                $lockedPayment->paid_at = now();
            }

            $lockedPayment->save();

            if ($nextStatus === PaymentStatus::Paid) {
                if ($order?->status === OrderStatus::Pending) {
                    $order->update(['status' => OrderStatus::Processing]);
                }

                return false;
            }

            if (
                $order?->status === OrderStatus::Pending
                && $nextStatus->restoresPendingOrder()
            ) {
                $restored = $this->orderInventory->restore($order->id);
                $order->update(['status' => OrderStatus::Cancelled]);

                return $restored;
            }

            return false;
        });

        if ($stockRestored) {
            CacheKeys::bump(CacheKeys::SOUVENIRS_VERSION);
        }

        if ($integrityFailure !== null) {
            Log::warning('Payment webhook failed amount or currency validation.', $integrityFailure + [
                'provider' => $provider->value,
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
            $payload['capture'] = PaymentPayload::capture($capture);

            $valid = $capture->status === PaymentWebhookStatus::Paid
                && hash_equals((string) $lockedPayment->provider_ref, $capture->providerRef)
                && PaymentAmount::matches(
                    (string) $lockedPayment->amount,
                    (string) $lockedPayment->currency,
                    $capture->amount,
                    $capture->currency,
                );

            if (! $valid) {
                $payload['capture_integrity_error'] = PaymentPayload::integrityFailure($lockedPayment, $capture);

                $lockedPayment->update(['payload_json' => $payload]);

                return 'integrity_failed';
            }

            unset(
                $payload['capture_integrity_error'],
                $payload['failure'],
                $payload['error'],
                $payload['capture_error'],
                $payload['gateway'],
            );

            if ($lockedPayment->status === PaymentStatus::Paid) {
                $lockedPayment->update(['payload_json' => $payload]);

                return 'already_paid';
            }

            if (! $lockedPayment->canTransitionTo(PaymentStatus::Paid)) {
                return 'transition_rejected';
            }

            $lockedPayment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'payload_json' => $payload,
            ]);

            if ($order?->status === OrderStatus::Pending) {
                $order->update(['status' => OrderStatus::Processing]);
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
