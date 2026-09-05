<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return list<self> */
    public function allowedUpdates(): array
    {
        return match ($this) {
            self::Pending => [self::Pending, self::Processing, self::Cancelled],
            self::Processing => [self::Processing, self::Completed, self::Cancelled],
            self::Completed => [self::Completed],
            self::Cancelled => [self::Cancelled],
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    /** @return list<string> */
    public static function revenueValues(): array
    {
        return [self::Processing->value, self::Completed->value];
    }
}
