<?php

namespace App\Services\Payments;

final class PaymentAmount
{
    public static function matches(
        string $expectedAmount,
        string $expectedCurrency,
        string $receivedAmount,
        string $receivedCurrency,
    ): bool {
        $expected = self::normalize($expectedAmount);
        $received = self::normalize($receivedAmount);
        $expectedCurrency = strtoupper(trim($expectedCurrency));
        $receivedCurrency = strtoupper(trim($receivedCurrency));

        return $expected !== null
            && $received !== null
            && preg_match('/^[A-Z]{3}$/', $expectedCurrency) === 1
            && $expectedCurrency === $receivedCurrency
            && $expected === $received;
    }

    private static function normalize(string $amount): ?string
    {
        $amount = trim($amount);

        if (preg_match('/^\+?([0-9]+)(?:\.([0-9]+))?$/', $amount, $matches) !== 1) {
            return null;
        }

        $whole = ltrim($matches[1], '0');
        $fraction = $matches[2] ?? '';

        if (strlen($fraction) > 2 && trim(substr($fraction, 2), '0') !== '') {
            return null;
        }

        return ($whole === '' ? '0' : $whole)
            .'.'
            .str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
