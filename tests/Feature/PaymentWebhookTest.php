<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_webhook_marks_payment_paid(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 150000,
            'status' => 'pending',
            'note' => 'Test order',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-TEST-123',
            'status' => 'pending',
            'amount' => 150000,
            'currency' => 'IDR',
        ]);

        config([
            'services.midtrans.server_key' => 'test-server-key',
        ]);

        $grossAmount = number_format($payment->amount, 2, '.', '');
        $signature = hash('sha512', $payment->provider_ref . '200' . $grossAmount . 'test-server-key');

        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-TEST-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $payload)
            ->assertOk();

        $this->postJson(route('payments.webhook.midtrans'), $payload)
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);

        $this->assertDatabaseCount('payment_webhook_events', 1);
        $this->assertDatabaseHas('payment_webhook_events', [
            'provider' => 'midtrans',
            'event_id' => 'TRX-TEST-001',
            'payment_id' => $payment->id,
        ]);
    }

    public function test_paypal_webhook_marks_payment_paid_and_idempotent(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 200000,
            'status' => 'pending',
            'note' => 'Test order PayPal',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-ORDER-123',
            'status' => 'pending',
            'amount' => 200000,
            'currency' => 'IDR',
        ]);

        config([
            'services.paypal.client_id' => 'paypal-client',
            'services.paypal.client_secret' => 'paypal-secret',
            'services.paypal.webhook_id' => 'paypal-webhook',
            'services.paypal.is_production' => false,
        ]);

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test-token',
            ], 200),
            'https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'SUCCESS',
            ], 200),
        ]);

        $payload = [
            'id' => 'WH-TEST-001',
            'event_type' => 'CHECKOUT.ORDER.COMPLETED',
            'resource' => [
                'id' => $payment->provider_ref,
                'amount' => [
                    'value' => '100.00',
                    'currency_code' => 'USD',
                ],
            ],
        ];

        $this->postJson(route('payments.webhook.paypal'), $payload)
            ->assertOk();

        $this->postJson(route('payments.webhook.paypal'), $payload)
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);

        $this->assertDatabaseCount('payment_webhook_events', 1);
        $this->assertDatabaseHas('payment_webhook_events', [
            'provider' => 'paypal',
            'event_id' => 'WH-TEST-001',
            'payment_id' => $payment->id,
        ]);
    }
}
