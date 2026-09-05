<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class PaymentPayload
{
    /** @return array<string, string> */
    public static function gateway(string $provider, PaymentGatewayResult $result): array
    {
        return self::present([
            'provider' => self::value($provider),
            'provider_ref' => self::value($result->providerRef),
            'status' => self::value($result->payload['status'] ?? null),
            'redirect_url' => self::value($result->redirectUrl, 2048),
        ]);
    }

    /** @return array<string, string> */
    public static function webhook(string $provider, PaymentWebhookData $data): array
    {
        $payload = [
            'event_id' => self::value($data->eventId),
            'provider_ref' => self::value($data->providerRef),
            'status' => self::value($data->status),
            'amount' => self::value($data->amount),
            'currency' => self::value($data->currency),
        ];

        if ($provider === 'midtrans') {
            $payload += [
                'provider_status' => self::value($data->payload['transaction_status'] ?? null),
                'fraud_status' => self::value($data->payload['fraud_status'] ?? null),
                'payment_type' => self::value($data->payload['payment_type'] ?? null),
                'status_code' => self::value($data->payload['status_code'] ?? null),
                'occurred_at' => self::value(
                    $data->payload['settlement_time'] ?? $data->payload['transaction_time'] ?? null
                ),
            ];
        }

        if ($provider === 'paypal') {
            $payload += [
                'event_type' => self::value($data->payload['event_type'] ?? null),
                'resource_type' => self::value($data->payload['resource_type'] ?? null),
                'resource_status' => self::value(Arr::get($data->payload, 'resource.status')),
                'occurred_at' => self::value($data->payload['create_time'] ?? null),
            ];
        }

        return self::present($payload);
    }

    /** @return array<string, string> */
    public static function capture(PaymentWebhookData $capture): array
    {
        return self::present([
            'event_id' => self::value($capture->eventId),
            'provider_ref' => self::value($capture->providerRef),
            'status' => self::value($capture->status),
            'amount' => self::value($capture->amount),
            'currency' => self::value($capture->currency),
            'provider_status' => self::value($capture->payload['status'] ?? null),
            'capture_status' => self::value(Arr::get($capture->payload, 'purchase_units.0.payments.captures.0.status')),
        ]);
    }

    /** @return array<string, string> */
    public static function integrityFailure(Payment $payment, PaymentWebhookData $received): array
    {
        return self::present([
            'expected_provider_ref' => self::value($payment->provider_ref),
            'received_provider_ref' => self::value($received->providerRef),
            'expected_amount' => self::value($payment->amount),
            'received_amount' => self::value($received->amount),
            'expected_currency' => self::value($payment->currency),
            'received_currency' => self::value($received->currency),
        ]);
    }

    /** @return array{code: string} */
    public static function failure(string $code): array
    {
        return ['code' => self::value($code) ?? 'payment_error'];
    }

    private static function value(mixed $value, int $limit = 255): ?string
    {
        if (! is_scalar($value) && ! $value instanceof \Stringable) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : Str::limit($value, $limit, '');
    }

    /**
     * @param  array<string, string|null>  $payload
     * @return array<string, string>
     */
    private static function present(array $payload): array
    {
        return array_filter($payload, fn (?string $value): bool => $value !== null);
    }
}
