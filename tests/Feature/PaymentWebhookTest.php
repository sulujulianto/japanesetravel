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
        $signature = hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key');

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
            'event_id' => 'TRX-TEST-001:paid',
            'payment_id' => $payment->id,
        ]);
    }

    public function test_midtrans_pending_then_settlement_with_same_transaction_id_is_processed(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 175000,
            'status' => 'pending',
            'note' => 'Test order progression',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-TEST-PROGRESSION',
            'status' => 'pending',
            'amount' => 175000,
            'currency' => 'IDR',
        ]);

        config([
            'services.midtrans.server_key' => 'test-server-key',
        ]);

        $grossAmount = number_format($payment->amount, 2, '.', '');
        $signature = hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key');

        $pendingPayload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-PROGRESSION-001',
        ];

        $settlementPayload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-PROGRESSION-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $pendingPayload)
            ->assertOk();

        $this->postJson(route('payments.webhook.midtrans'), $settlementPayload)
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);

        $this->assertDatabaseCount('payment_webhook_events', 2);
        $this->assertDatabaseHas('payment_webhook_events', [
            'provider' => 'midtrans',
            'event_id' => 'TRX-PROGRESSION-001:pending',
            'payment_id' => $payment->id,
        ]);
        $this->assertDatabaseHas('payment_webhook_events', [
            'provider' => 'midtrans',
            'event_id' => 'TRX-PROGRESSION-001:paid',
            'payment_id' => $payment->id,
        ]);
    }

    public function test_midtrans_duplicate_same_status_event_is_idempotent(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 155000,
            'status' => 'pending',
            'note' => 'Test duplicate event',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-TEST-DUPLICATE',
            'status' => 'pending',
            'amount' => 155000,
            'currency' => 'IDR',
        ]);

        config([
            'services.midtrans.server_key' => 'test-server-key',
        ]);

        $grossAmount = number_format($payment->amount, 2, '.', '');
        $signature = hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key');

        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-DUP-001',
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
            'event_id' => 'TRX-DUP-001:paid',
            'payment_id' => $payment->id,
        ]);
    }

    public function test_paid_webhook_does_not_downgrade_completed_order(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 300000,
            'status' => 'completed',
            'note' => 'Completed order',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-TEST-COMPLETED-PAID',
            'status' => 'pending',
            'amount' => 300000,
            'currency' => 'IDR',
        ]);

        config([
            'services.midtrans.server_key' => 'test-server-key',
        ]);

        $grossAmount = number_format($payment->amount, 2, '.', '');
        $signature = hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key');

        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-COMPLETED-PAID-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $payload)
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_refunded_webhook_does_not_cancel_completed_or_processing_order(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $completedOrder = Order::create([
            'user_id' => $user->id,
            'total_price' => 250000,
            'status' => 'completed',
            'note' => 'Completed order',
        ]);
        $processingOrder = Order::create([
            'user_id' => $user->id,
            'total_price' => 260000,
            'status' => 'processing',
            'note' => 'Processing order',
        ]);

        $completedPayment = Payment::create([
            'order_id' => $completedOrder->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-TEST-COMPLETED-REFUND',
            'status' => 'paid',
            'amount' => 250000,
            'currency' => 'IDR',
        ]);
        $processingPayment = Payment::create([
            'order_id' => $processingOrder->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-TEST-PROCESSING-REFUND',
            'status' => 'paid',
            'amount' => 260000,
            'currency' => 'IDR',
        ]);

        config([
            'services.midtrans.server_key' => 'test-server-key',
        ]);

        $completedGrossAmount = number_format($completedPayment->amount, 2, '.', '');
        $completedSignature = hash('sha512', $completedPayment->provider_ref.'200'.$completedGrossAmount.'test-server-key');
        $processingGrossAmount = number_format($processingPayment->amount, 2, '.', '');
        $processingSignature = hash('sha512', $processingPayment->provider_ref.'200'.$processingGrossAmount.'test-server-key');

        $completedPayload = [
            'order_id' => $completedPayment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $completedGrossAmount,
            'signature_key' => $completedSignature,
            'transaction_status' => 'refund',
            'fraud_status' => 'accept',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-COMPLETED-REFUND-001',
        ];
        $processingPayload = [
            'order_id' => $processingPayment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $processingGrossAmount,
            'signature_key' => $processingSignature,
            'transaction_status' => 'refund',
            'fraud_status' => 'accept',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-PROCESSING-REFUND-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $completedPayload)
            ->assertOk();
        $this->postJson(route('payments.webhook.midtrans'), $processingPayload)
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $completedOrder->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $processingOrder->id,
            'status' => 'processing',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $completedPayment->id,
            'status' => 'refunded',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $processingPayment->id,
            'status' => 'refunded',
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

    public function test_paypal_return_capture_failure_does_not_throw_500_and_marks_payment_failed(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 210000,
            'status' => 'pending',
            'note' => 'PayPal return failure test',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-RETURN-FAIL-001',
            'status' => 'pending',
            'amount' => 210000,
            'currency' => 'USD',
            'payload_json' => [
                'gateway' => [
                    'id' => 'PAYPAL-RETURN-FAIL-001',
                ],
            ],
        ]);

        config([
            'services.paypal.client_id' => 'paypal-client',
            'services.paypal.client_secret' => 'paypal-secret',
            'services.paypal.is_production' => false,
        ]);

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test-token',
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'name' => 'UNPROCESSABLE_ENTITY',
            ], 422),
        ]);

        $response = $this->actingAs($user)
            ->get(route('payments.paypal.return', [
                'token' => $payment->provider_ref,
            ]));

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
        ]);

        $freshPayment = $payment->fresh();
        $this->assertNotNull($freshPayment);
        $this->assertStringContainsString('PayPal gagal menangkap pembayaran.', (string) ($freshPayment->payload_json['capture_error'] ?? ''));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_paypal_return_capture_success_marks_payment_paid_and_order_processing(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 220000,
            'status' => 'pending',
            'note' => 'PayPal return success test',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-RETURN-SUCCESS-001',
            'status' => 'pending',
            'amount' => 220000,
            'currency' => 'USD',
        ]);

        config([
            'services.paypal.client_id' => 'paypal-client',
            'services.paypal.client_secret' => 'paypal-secret',
            'services.paypal.is_production' => false,
        ]);

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test-token',
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'status' => 'COMPLETED',
                'id' => $payment->provider_ref,
            ], 201),
        ]);

        $response = $this->actingAs($user)
            ->get(route('payments.paypal.return', [
                'token' => $payment->provider_ref,
            ]));

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }
}
