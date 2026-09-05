<?php

namespace App\Enums;

enum PaymentWebhookStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';
    case Ignored = 'ignored';

    public function paymentStatus(): ?PaymentStatus
    {
        return PaymentStatus::tryFrom($this->value);
    }
}
