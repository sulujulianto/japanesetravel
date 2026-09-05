<?php

namespace Tests\Unit\Enums;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookStatus;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class DomainStateTest extends TestCase
{
    public function test_order_status_exposes_valid_updates(): void
    {
        $this->assertSame(
            [OrderStatus::Pending, OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::Pending->allowedUpdates(),
        );
        $this->assertSame([OrderStatus::Completed], OrderStatus::Completed->allowedUpdates());
    }

    public function test_payment_status_exposes_valid_transitions_and_restoration_semantics(): void
    {
        $this->assertSame(
            [PaymentStatus::Paid, PaymentStatus::Failed, PaymentStatus::Expired, PaymentStatus::Refunded],
            PaymentStatus::Pending->allowedTransitions(),
        );
        $this->assertTrue(PaymentStatus::Expired->restoresPendingOrder());
        $this->assertFalse(PaymentStatus::Paid->restoresPendingOrder());
    }

    public function test_ignored_webhook_result_is_not_a_payment_status(): void
    {
        $this->assertNull(PaymentWebhookStatus::Ignored->paymentStatus());
        $this->assertSame(PaymentStatus::Paid, PaymentWebhookStatus::Paid->paymentStatus());
    }

    public function test_backed_values_match_persisted_contracts(): void
    {
        $this->assertSame(
            ['pending', 'processing', 'completed', 'cancelled'],
            OrderStatus::values(),
        );
        $this->assertSame(
            ['pending', 'paid', 'failed', 'expired', 'refunded'],
            PaymentStatus::values(),
        );
        $this->assertSame(['midtrans', 'paypal'], PaymentProvider::values());
        $this->assertSame('user', UserRole::User->value);
        $this->assertSame('admin', UserRole::Admin->value);
    }
}
