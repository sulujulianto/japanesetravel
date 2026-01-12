<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PaymentGatewayResult;
use App\Services\Payments\PaymentWebhookData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransSnapDriver implements PaymentGatewayInterface
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createPayment(Order $order, Payment $payment): PaymentGatewayResult
    {
        $order->loadMissing(['items', 'user']);

        $items = $order->items->map(function ($item) {
            $name = $item->product_name ?? $item->product?->name ?? 'Item';

            return [
                'id' => (string) $item->id,
                'price' => (int) round($item->price),
                'quantity' => (int) $item->quantity,
                'name' => Str::limit($name, 50, ''),
            ];
        })->values()->all();

        $transaction = [
            'transaction_details' => [
                'order_id' => $payment->provider_ref,
                'gross_amount' => (int) round($order->total_price),
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->user?->username ?? 'Customer',
                'email' => $order->user?->email ?? null,
            ],
        ];

        $response = Snap::createTransaction($transaction);
        $payload = json_decode(json_encode($response), true) ?? [];

        if (empty($response->redirect_url)) {
            throw new \RuntimeException('Midtrans redirect URL tidak tersedia.');
        }

        return new PaymentGatewayResult(
            providerRef: $payment->provider_ref,
            redirectUrl: $response->redirect_url ?? null,
            token: $response->token ?? null,
            payload: $payload,
            currency: 'IDR',
            amount: (float) $order->total_price,
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        $signature = (string) $request->input('signature_key', '');
        $orderId = (string) $request->input('order_id', '');
        $statusCode = (string) $request->input('status_code', '');
        $grossAmount = (string) $request->input('gross_amount', '');
        $serverKey = (string) config('services.midtrans.server_key');

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request): PaymentWebhookData
    {
        $payload = $request->all();
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $status = match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? 'pending' : 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'expired',
            'cancel' => 'failed',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };

        $eventId = (string) ($payload['transaction_id'] ?? '');
        if ($eventId === '') {
            $orderId = (string) ($payload['order_id'] ?? '');
            $eventId = $orderId !== '' ? $orderId . ':' . $transactionStatus : '';
        }

        return new PaymentWebhookData(
            providerRef: (string) ($payload['order_id'] ?? ''),
            status: $status,
            amount: (float) ($payload['gross_amount'] ?? 0),
            currency: (string) ($payload['currency'] ?? 'IDR'),
            payload: $payload,
            eventId: $eventId,
        );
    }
}
