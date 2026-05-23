<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\Payments\Drivers\PayPalCheckoutDriver;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PaymentWebhookData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
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

            if ($payment->status === 'pending') {
                $payload = $payment->payload_json ?? [];
                $payload['capture_error'] = $exception->getMessage();

                $payment->update([
                    'status' => 'failed',
                    'payload_json' => $payload,
                ]);
            }

            return $this->redirectToOrderError(
                $payment,
                __('Gagal memproses pembayaran PayPal. Silakan coba lagi atau hubungi admin.')
            );
        }

        $status = $response['status'] ?? '';

        $message = __('Pembayaran berhasil diproses.');

        if ($status === 'COMPLETED' && $payment->status !== 'paid') {
            DB::transaction(function () use ($payment, $response) {
                $payload = $payment->payload_json ?? [];
                $payload['capture'] = $response;

                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payload_json' => $payload,
                ]);

                $this->applyOrderStatusGuardFromWebhook($payment, 'paid');
            });
        } elseif ($status !== 'COMPLETED') {
            $message = __('Pembayaran sedang diproses.');
        }

        return $this->redirectToOrder($payment, $message);
    }

    public function paypalCancel(Request $request): RedirectResponse
    {
        $providerRef = (string) $request->query('token', '');

        if ($providerRef !== '') {
            $payment = Payment::where('provider', 'paypal')->where('provider_ref', $providerRef)->first();
            if ($payment && $payment->status === 'pending') {
                $payment->update([
                    'status' => 'failed',
                ]);
            }
        }

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
        DB::transaction(function () use ($payment, $data, $provider) {
            $payment->refresh()->loadMissing('order');

            if ($data->eventId !== '') {
                $event = PaymentWebhookEvent::firstOrCreate(
                    [
                        'provider' => $provider,
                        'event_id' => $data->eventId,
                    ],
                    [
                        'payment_id' => $payment->id,
                        'status' => $data->status,
                        'payload_json' => $data->payload,
                    ]
                );

                if (! $event->wasRecentlyCreated) {
                    return;
                }
            }

            if ($payment->status === $data->status) {
                return;
            }

            $payload = $payment->payload_json ?? [];
            $payload['webhook'] = $data->payload;

            $payment->status = $data->status;
            $payment->amount = $data->amount ?: $payment->amount;
            $payment->currency = $data->currency ?: $payment->currency;
            $payment->payload_json = $payload;

            if ($data->status === 'paid') {
                $payment->paid_at = now();
            }

            $this->applyOrderStatusGuardFromWebhook($payment, $data->status);

            $payment->save();
        });
    }

    protected function applyOrderStatusGuardFromWebhook(Payment $payment, string $paymentStatus): void
    {
        $order = $payment->order()->first();
        if (! $order) {
            return;
        }

        if ($paymentStatus === 'paid') {
            if ($order->status === 'pending') {
                $order->update(['status' => 'processing']);
            }

            return;
        }

        if (in_array($paymentStatus, ['failed', 'expired', 'cancelled', 'refunded'], true) && $order->status === 'pending') {
            $order->update(['status' => 'cancelled']);
        }
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
