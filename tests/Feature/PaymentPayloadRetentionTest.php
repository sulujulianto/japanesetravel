<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPayloadRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_prunes_expired_payloads_without_deleting_audit_records(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-05 12:00:00'));

        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'total_price' => 100000,
            'status' => 'completed',
        ]);
        $oldTerminal = $this->payment($order, 'paid', 'OLD-TERMINAL', now()->subDays(45));
        $oldPending = $this->payment($order, 'pending', 'OLD-PENDING', now()->subDays(45));
        $recentTerminal = $this->payment($order, 'failed', 'RECENT-TERMINAL', now()->subDays(5));

        $oldEvent = $this->event($oldTerminal, 'OLD-EVENT', now()->subDays(45));
        $recentEvent = $this->event($recentTerminal, 'RECENT-EVENT', now()->subDays(5));

        $this->artisan('payments:prune-payloads', ['--days' => 30])
            ->expectsOutputToContain('Pruned 2 payment payloads and 1 webhook payloads older than 30 days.')
            ->assertExitCode(Command::SUCCESS);

        $this->assertNull($oldTerminal->fresh()?->payload_json);
        $this->assertNull($oldPending->fresh()?->payload_json);
        $this->assertNotNull($recentTerminal->fresh()?->payload_json);
        $this->assertNull($oldEvent->fresh()?->payload_json);
        $this->assertNotNull($recentEvent->fresh()?->payload_json);

        $this->assertDatabaseCount('payments', 3);
        $this->assertDatabaseCount('payment_webhook_events', 2);
        $this->assertDatabaseHas('payments', [
            'id' => $oldTerminal->id,
            'status' => 'paid',
            'provider_ref' => 'OLD-TERMINAL',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $oldPending->id,
            'status' => 'pending',
            'provider_ref' => 'OLD-PENDING',
        ]);
        $this->assertDatabaseHas('payment_webhook_events', [
            'id' => $oldEvent->id,
            'event_id' => 'OLD-EVENT',
            'status' => 'paid',
        ]);
    }

    public function test_command_rejects_an_invalid_retention_override(): void
    {
        $this->artisan('payments:prune-payloads', ['--days' => 0])
            ->expectsOutputToContain('The retention period must be an integer between 1 and 3650 days.')
            ->assertExitCode(Command::FAILURE);
    }

    private function payment(Order $order, string $status, string $providerRef, \DateTimeInterface $timestamp): Payment
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => $providerRef,
            'status' => $status,
            'amount' => 100000,
            'currency' => 'IDR',
            'payload_json' => ['gateway' => ['redirect_url' => 'https://pay.example.test/'.$providerRef]],
        ]);
        $payment->forceFill([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->saveQuietly();

        return $payment;
    }

    private function event(Payment $payment, string $eventId, \DateTimeInterface $timestamp): PaymentWebhookEvent
    {
        $event = PaymentWebhookEvent::create([
            'payment_id' => $payment->id,
            'provider' => 'midtrans',
            'event_id' => $eventId,
            'status' => 'paid',
            'payload_json' => ['event_id' => $eventId],
        ]);
        $event->forceFill([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->saveQuietly();

        return $event;
    }
}
