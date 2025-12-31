<?php

namespace App\Services\Payments;

class PaymentGatewayResult
{
    public function __construct(
        public string $providerRef,
        public ?string $redirectUrl = null,
        public ?string $token = null,
        public array $payload = [],
        public ?string $currency = null,
        public ?float $amount = null,
    ) {
    }
}
