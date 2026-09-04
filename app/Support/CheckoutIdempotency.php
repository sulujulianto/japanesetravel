<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

final class CheckoutIdempotency
{
    public const SESSION_KEY = 'checkout.idempotency_token';

    public function issue(): string
    {
        $token = Session::get(self::SESSION_KEY);

        if (is_string($token) && Str::isUuid($token)) {
            return $token;
        }

        return $this->rotate();
    }

    public function rotate(): string
    {
        $token = (string) Str::uuid();
        Session::put(self::SESSION_KEY, $token);

        return $token;
    }

    public function forget(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function matches(string $token): bool
    {
        $sessionToken = Session::get(self::SESSION_KEY);

        return is_string($sessionToken) && hash_equals($sessionToken, $token);
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
