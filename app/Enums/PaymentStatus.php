<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Paid, self::Failed, self::Expired, self::Refunded],
            self::Failed, self::Expired => [self::Paid],
            self::Paid => [self::Refunded],
            self::Refunded => [],
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function restoresPendingOrder(): bool
    {
        return in_array($this, [self::Failed, self::Expired, self::Refunded], true);
    }
}
