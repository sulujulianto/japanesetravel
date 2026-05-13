<?php

namespace App\Services\Payments;

use App\Services\Payments\Drivers\MidtransSnapDriver;
use App\Services\Payments\Drivers\PayPalCheckoutDriver;
use InvalidArgumentException;

class PaymentService
{
    public function driver(string $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            'midtrans' => app(MidtransSnapDriver::class),
            'paypal' => app(PayPalCheckoutDriver::class),
            default => throw new InvalidArgumentException('Unsupported payment provider.'),
        };
    }
}
