<?php

namespace App\Services\Payments;

use App\Enums\PaymentProvider;
use App\Services\Payments\Drivers\MidtransSnapDriver;
use App\Services\Payments\Drivers\PayPalCheckoutDriver;
use InvalidArgumentException;

class PaymentService
{
    public function driver(PaymentProvider|string $provider): PaymentGatewayInterface
    {
        $provider = is_string($provider) ? PaymentProvider::tryFrom($provider) : $provider;

        return match ($provider) {
            PaymentProvider::Midtrans => app(MidtransSnapDriver::class),
            PaymentProvider::PayPal => app(PayPalCheckoutDriver::class),
            default => throw new InvalidArgumentException('Unsupported payment provider.'),
        };
    }
}
