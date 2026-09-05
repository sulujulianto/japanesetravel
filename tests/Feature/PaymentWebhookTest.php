<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
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
            'amount' => 13.33,
            'currency' => 'USD',
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
                    'value' => '13.33',
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
            'amount' => 14.67,
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
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(
                $this->completedPayPalCapture($payment),
                201
            ),
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

    public function test_paypal_return_capture_success_does_not_downgrade_completed_order(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 230000,
            'status' => 'completed',
            'note' => 'PayPal return completed order test',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-RETURN-COMPLETED-001',
            'status' => 'pending',
            'amount' => 15.33,
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
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(
                $this->completedPayPalCapture($payment),
                201
            ),
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
            'status' => 'completed',
        ]);
    }

    public function test_paypal_return_capture_success_does_not_revive_cancelled_order(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 240000,
            'status' => 'cancelled',
            'note' => 'PayPal return cancelled order test',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-RETURN-CANCELLED-001',
            'status' => 'pending',
            'amount' => 16,
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
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(
                $this->completedPayPalCapture($payment),
                201
            ),
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
            'status' => 'cancelled',
        ]);
    }

    public function test_expired_webhook_cancels_order_and_restores_stock_exactly_once(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $souvenir = Souvenir::factory()->create(['stock' => 3]);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 200000,
            'status' => 'pending',
            'note' => 'Expired payment inventory test',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'souvenir_id' => $souvenir->id,
            'quantity' => 2,
            'price' => 100000,
            'product_name' => 'Test souvenir',
            'product_price' => 100000,
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-EXPIRED-STOCK',
            'status' => 'pending',
            'amount' => 200000,
            'currency' => 'IDR',
        ]);
        config(['services.midtrans.server_key' => 'test-server-key']);

        $grossAmount = number_format($payment->amount, 2, '.', '');
        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key'),
            'transaction_status' => 'expire',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-EXPIRED-STOCK-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();
        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $this->assertSame('expired', $payment->fresh()->status);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->stock_restored_at);
        $this->assertSame(5, $souvenir->fresh()->stock);
        $this->assertDatabaseCount('payment_webhook_events', 1);
        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertDatabaseHas('inventory_movements', [
            'souvenir_id' => $souvenir->id,
            'order_id' => $order->id,
            'actor_id' => null,
            'type' => InventoryMovement::TYPE_ORDER_RESTORATION,
            'quantity_delta' => 2,
            'stock_before' => 3,
            'stock_after' => 5,
            'reference' => 'order:'.$order->id.':restoration:souvenir:'.$souvenir->id,
        ]);
    }

    public function test_late_pending_webhook_does_not_downgrade_paid_payment(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 150000,
            'status' => 'processing',
            'note' => 'Late pending test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-LATE-PENDING',
            'status' => 'paid',
            'amount' => 150000,
            'currency' => 'IDR',
            'paid_at' => now(),
        ]);
        config(['services.midtrans.server_key' => 'test-server-key']);

        $grossAmount = number_format($payment->amount, 2, '.', '');
        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key'),
            'transaction_status' => 'pending',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-LATE-PENDING-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_unknown_webhook_event_is_recorded_without_mutating_payment(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 125000,
            'status' => 'pending',
            'note' => 'Unknown event test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-UNKNOWN-EVENT',
            'status' => 'pending',
            'amount' => 125000,
            'currency' => 'IDR',
        ]);
        config(['services.midtrans.server_key' => 'test-server-key']);

        $grossAmount = number_format($payment->amount, 2, '.', '');
        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key'),
            'transaction_status' => 'future-provider-status',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-UNKNOWN-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseHas('payment_webhook_events', [
            'provider' => 'midtrans',
            'event_id' => 'TRX-UNKNOWN-001:ignored',
            'status' => 'ignored',
        ]);
    }

    public function test_refunded_payment_cannot_be_downgraded_by_late_paid_webhook(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 175000,
            'status' => 'completed',
            'note' => 'Late paid after refund test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-REFUNDED-LATE-PAID',
            'status' => 'refunded',
            'amount' => 175000,
            'currency' => 'IDR',
            'paid_at' => now(),
        ]);
        config(['services.midtrans.server_key' => 'test-server-key']);

        $grossAmount = number_format($payment->amount, 2, '.', '');
        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key'),
            'transaction_status' => 'settlement',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-REFUNDED-LATE-PAID-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_paypal_cancel_callback_does_not_mutate_financial_state(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 100000,
            'status' => 'pending',
            'note' => 'PayPal cancel callback test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-CANCEL-001',
            'status' => 'pending',
            'amount' => 10,
            'currency' => 'USD',
        ]);

        $this->get(route('payments.paypal.cancel', ['token' => $payment->provider_ref]))
            ->assertRedirect(route('orders.index'))
            ->assertSessionHas('error');

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_midtrans_paid_webhook_with_wrong_amount_is_recorded_without_mutating_financial_state(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 150000,
            'status' => 'pending',
            'note' => 'Wrong Midtrans amount test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-WRONG-AMOUNT',
            'status' => 'pending',
            'amount' => 150000,
            'currency' => 'IDR',
        ]);
        config(['services.midtrans.server_key' => 'test-server-key']);

        $grossAmount = '149999.00';
        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key'),
            'transaction_status' => 'settlement',
            'currency' => 'IDR',
            'transaction_id' => 'TRX-WRONG-AMOUNT-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $freshPayment = $payment->fresh();
        $this->assertNotNull($freshPayment);
        $this->assertSame('pending', $freshPayment->status);
        $this->assertSame('150000.00', $freshPayment->amount);
        $this->assertSame('IDR', $freshPayment->currency);
        $this->assertNull($freshPayment->paid_at);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseHas('payment_webhook_events', [
            'payment_id' => $payment->id,
            'provider' => 'midtrans',
            'event_id' => 'TRX-WRONG-AMOUNT-001:paid',
            'status' => 'paid',
        ]);
    }

    public function test_midtrans_paid_webhook_with_wrong_currency_is_recorded_without_mutating_financial_state(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 150000,
            'status' => 'pending',
            'note' => 'Wrong Midtrans currency test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-WRONG-CURRENCY',
            'status' => 'pending',
            'amount' => 150000,
            'currency' => 'IDR',
        ]);
        config(['services.midtrans.server_key' => 'test-server-key']);

        $grossAmount = '150000.00';
        $payload = [
            'order_id' => $payment->provider_ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $payment->provider_ref.'200'.$grossAmount.'test-server-key'),
            'transaction_status' => 'settlement',
            'currency' => 'USD',
            'transaction_id' => 'TRX-WRONG-CURRENCY-001',
        ];

        $this->postJson(route('payments.webhook.midtrans'), $payload)->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('IDR', $payment->fresh()->currency);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseHas('payment_webhook_events', [
            'payment_id' => $payment->id,
            'event_id' => 'TRX-WRONG-CURRENCY-001:paid',
        ]);
    }

    public function test_paypal_paid_webhook_with_wrong_currency_is_recorded_without_mutating_financial_state(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 200000,
            'status' => 'pending',
            'note' => 'Wrong PayPal currency test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-WRONG-CURRENCY',
            'status' => 'pending',
            'amount' => 13.33,
            'currency' => 'USD',
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
            ]),
            'https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'SUCCESS',
            ]),
        ]);

        $payload = [
            'id' => 'WH-WRONG-CURRENCY-001',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE-WRONG-CURRENCY-001',
                'supplementary_data' => [
                    'related_ids' => ['order_id' => $payment->provider_ref],
                ],
                'amount' => [
                    'value' => '13.33',
                    'currency_code' => 'EUR',
                ],
            ],
        ];

        $this->postJson(route('payments.webhook.paypal'), $payload)->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('13.33', $payment->fresh()->amount);
        $this->assertSame('USD', $payment->fresh()->currency);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseHas('payment_webhook_events', [
            'payment_id' => $payment->id,
            'provider' => 'paypal',
            'event_id' => 'WH-WRONG-CURRENCY-001',
            'status' => 'paid',
        ]);
    }

    public function test_paypal_return_rejects_completed_capture_with_wrong_amount(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 220000,
            'status' => 'pending',
            'note' => 'Wrong PayPal return amount test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-RETURN-WRONG-AMOUNT',
            'status' => 'pending',
            'amount' => 14.67,
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
            ]),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(
                $this->completedPayPalCapture($payment, amount: '14.66'),
                201
            ),
        ]);

        $this->actingAs($user)
            ->get(route('payments.paypal.return', ['token' => $payment->provider_ref]))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $freshPayment = $payment->fresh();
        $this->assertNotNull($freshPayment);
        $this->assertSame('pending', $freshPayment->status);
        $this->assertSame('14.67', $freshPayment->amount);
        $this->assertNull($freshPayment->paid_at);
        $this->assertSame('14.66', $freshPayment->payload_json['capture_integrity_error']['received_amount'] ?? null);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_paypal_return_rejects_completed_response_without_capture_details(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 220000,
            'status' => 'pending',
            'note' => 'Missing PayPal capture details test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-RETURN-MISSING-CAPTURE',
            'status' => 'pending',
            'amount' => 14.67,
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
            ]),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'id' => $payment->provider_ref,
                'status' => 'COMPLETED',
            ], 201),
        ]);

        $this->actingAs($user)
            ->get(route('payments.paypal.return', ['token' => $payment->provider_ref]))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame('', $payment->fresh()->payload_json['capture_integrity_error']['received_amount'] ?? null);
    }

    public function test_paypal_return_rejects_capture_for_different_provider_reference(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 220000,
            'status' => 'pending',
            'note' => 'Wrong PayPal reference test',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'paypal',
            'provider_ref' => 'PAYPAL-RETURN-EXPECTED-REF',
            'status' => 'pending',
            'amount' => 14.67,
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
            ]),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(
                $this->completedPayPalCapture($payment, providerRef: 'PAYPAL-RETURN-OTHER-REF'),
                201
            ),
        ]);

        $this->actingAs($user)
            ->get(route('payments.paypal.return', ['token' => $payment->provider_ref]))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(
            'PAYPAL-RETURN-OTHER-REF',
            $payment->fresh()->payload_json['capture_integrity_error']['received_provider_ref'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function completedPayPalCapture(
        Payment $payment,
        ?string $amount = null,
        ?string $currency = null,
        ?string $providerRef = null,
    ): array {
        return [
            'id' => $providerRef ?? $payment->provider_ref,
            'status' => 'COMPLETED',
            'purchase_units' => [
                [
                    'payments' => [
                        'captures' => [
                            [
                                'id' => 'CAPTURE-'.$payment->id,
                                'status' => 'COMPLETED',
                                'amount' => [
                                    'value' => $amount ?? (string) $payment->amount,
                                    'currency_code' => $currency ?? $payment->currency,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
