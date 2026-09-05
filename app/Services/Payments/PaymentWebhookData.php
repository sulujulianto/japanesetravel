<?php

namespace App\Services\Payments;

class PaymentWebhookData
{
    public function __construct(
        public string $providerRef,
        public string $status,
        public string $amount,
        public string $currency,
        public array $payload = [],
        public string $eventId = '',
    ) {}
}
