<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PaymentGatewayResult;
use App\Services\Payments\PaymentWebhookData;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RuntimeException;

class PayPalCheckoutDriver implements PaymentGatewayInterface
{
    protected Client $client;
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $webhookId;
    protected string $currency;
    protected float $exchangeRate;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('services.paypal.is_production')
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
        $this->clientId = (string) config('services.paypal.client_id');
        $this->clientSecret = (string) config('services.paypal.client_secret');
        $this->webhookId = (string) config('services.paypal.webhook_id');
        $this->currency = (string) config('services.paypal.currency', 'USD');
        $this->exchangeRate = (float) config('services.paypal.exchange_rate', 15000);
    }

    public function createPayment(Order $order, Payment $payment): PaymentGatewayResult
    {
        $accessToken = $this->getAccessToken();
        $amount = $this->convertAmount((float) $order->total_price);

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => (string) $order->id,
                    'description' => 'Japan Travel Order #' . $order->id,
                    'amount' => [
                        'currency_code' => $this->currency,
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                ],
            ],
            'application_context' => [
                'return_url' => route('payments.paypal.return'),
                'cancel_url' => route('payments.paypal.cancel'),
            ],
        ];

        $response = $this->client->post($this->baseUrl . '/v2/checkout/orders', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $providerRef = $body['id'] ?? '';
        $redirectUrl = collect($body['links'] ?? [])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (! $providerRef || ! $redirectUrl) {
            throw new RuntimeException('PayPal checkout link tidak tersedia.');
        }

        return new PaymentGatewayResult(
            providerRef: $providerRef,
            redirectUrl: $redirectUrl,
            token: null,
            payload: $body ?? [],
            currency: $this->currency,
            amount: $amount,
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        if (! $this->webhookId) {
            return false;
        }

        $accessToken = $this->getAccessToken();

        $payload = [
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'webhook_id' => $this->webhookId,
            'webhook_event' => $request->all(),
        ];

        $response = $this->client->post($this->baseUrl . '/v1/notifications/verify-webhook-signature', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = json_decode((string) $response->getBody(), true);

        return ($body['verification_status'] ?? '') === 'SUCCESS';
    }

    public function parseWebhook(Request $request): PaymentWebhookData
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? '';
        $resource = $payload['resource'] ?? [];

        $providerRef = Arr::get($resource, 'supplementary_data.related_ids.order_id')
            ?? Arr::get($resource, 'id', '');

        $status = match ($eventType) {
            'PAYMENT.CAPTURE.COMPLETED',
            'CHECKOUT.ORDER.COMPLETED' => 'paid',
            'PAYMENT.CAPTURE.DENIED' => 'failed',
            'PAYMENT.CAPTURE.REFUNDED' => 'refunded',
            'CHECKOUT.ORDER.APPROVED' => 'pending',
            'CHECKOUT.ORDER.CANCELLED' => 'failed',
            default => 'pending',
        };

        $amountValue = Arr::get($resource, 'amount.value')
            ?? Arr::get($resource, 'purchase_units.0.amount.value')
            ?? 0;
        $currency = Arr::get($resource, 'amount.currency_code')
            ?? Arr::get($resource, 'purchase_units.0.amount.currency_code')
            ?? $this->currency;

        return new PaymentWebhookData(
            providerRef: (string) $providerRef,
            status: $status,
            amount: (float) $amountValue,
            currency: (string) $currency,
            payload: $payload,
        );
    }

    public function captureOrder(string $providerRef): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->client->post($this->baseUrl . '/v2/checkout/orders/' . $providerRef . '/capture', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    protected function getAccessToken(): string
    {
        if (! $this->clientId || ! $this->clientSecret) {
            throw new RuntimeException('Konfigurasi PayPal belum lengkap.');
        }

        $response = $this->client->post($this->baseUrl . '/v1/oauth2/token', [
            'auth' => [$this->clientId, $this->clientSecret],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $token = $body['access_token'] ?? null;

        if (! $token) {
            throw new RuntimeException('Gagal mendapatkan token PayPal.');
        }

        return $token;
    }

    protected function convertAmount(float $amountIdr): float
    {
        if ($this->currency === 'IDR' || $this->exchangeRate <= 0) {
            return round($amountIdr, 2);
        }

        return round($amountIdr / $this->exchangeRate, 2);
    }
}
