<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Midtrans = 'midtrans';
    case PayPal = 'paypal';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $provider): string => $provider->value, self::cases());
    }
}
