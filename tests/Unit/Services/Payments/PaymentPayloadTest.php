<?php

namespace Tests\Unit\Services\Payments;

use App\Models\Payment;
use App\Services\Payments\PaymentGatewayResult;
use App\Services\Payments\PaymentPayload;
use App\Services\Payments\PaymentWebhookData;
use PHPUnit\Framework\TestCase;

class PaymentPayloadTest extends TestCase
{
    public function test_gateway_payload_keeps_redirect_fields_only(): void
    {
        $result = new PaymentGatewayResult(
            providerRef: 'PROVIDER-123',
            redirectUrl: 'https://pay.example.test/approve?token=opaque',
            token: 'secret-token',
            payload: [
                'status' => 'CREATED',
                'access_token' => 'never-store-this',
                'payer' => ['email_address' => 'private@example.test'],
            ],
        );

        $payload = PaymentPayload::gateway('paypal', $result);

        $this->assertSame([
            'provider' => 'paypal',
            'provider_ref' => 'PROVIDER-123',
            'status' => 'CREATED',
            'redirect_url' => 'https://pay.example.test/approve?token=opaque',
        ], $payload);
        $this->assertStringNotContainsString('secret-token', json_encode($payload, JSON_THROW_ON_ERROR));
        $this->assertStringNotContainsString('private@example.test', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    public function test_webhook_payload_is_a_bounded_provider_allowlist(): void
    {
        $data = new PaymentWebhookData(
            providerRef: 'ORDER-123',
            status: 'paid',
            amount: '150000.00',
            currency: 'IDR',
            payload: [
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'signature_key' => 'sensitive-signature',
                'customer_details' => ['email' => 'private@example.test'],
            ],
            eventId: 'EVENT-123:paid',
        );

        $payload = PaymentPayload::webhook('midtrans', $data);
        $encoded = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->assertSame('settlement', $payload['provider_status']);
        $this->assertSame('accept', $payload['fraud_status']);
        $this->assertStringNotContainsString('sensitive-signature', $encoded);
        $this->assertStringNotContainsString('private@example.test', $encoded);
    }

    public function test_integrity_values_are_length_limited(): void
    {
        $payment = new Payment([
            'provider_ref' => 'EXPECTED',
            'amount' => '10.00',
            'currency' => 'USD',
        ]);
        $received = new PaymentWebhookData(
            providerRef: str_repeat('x', 500),
            status: 'paid',
            amount: '9.00',
            currency: 'USD',
        );

        $payload = PaymentPayload::integrityFailure($payment, $received);

        $this->assertSame(255, strlen($payload['received_provider_ref']));
        $this->assertSame('9.00', $payload['received_amount']);
    }
}
