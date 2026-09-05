<?php

namespace App\Services\Payments;

use App\Enums\PaymentWebhookStatus;

class PaymentWebhookData
{
    public PaymentWebhookStatus $status;

    public function __construct(
        public string $providerRef,
        PaymentWebhookStatus|string $status,
        public string $amount,
        public string $currency,
        public array $payload = [],
        public string $eventId = '',
    ) {
        $this->status = is_string($status) ? PaymentWebhookStatus::from($status) : $status;
    }
}
