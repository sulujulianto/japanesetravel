<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ];

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
    }
}
