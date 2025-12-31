<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CacheKeys
{
    public const PLACES_VERSION = 'places:version';
    public const SOUVENIRS_VERSION = 'souvenirs:version';
    public const REVIEWS_VERSION = 'reviews:version';

    public static function version(string $key, int $default = 1): int
    {
        return (int) Cache::get($key, $default);
    }

    public static function bump(string $key): int
    {
        $next = self::version($key) + 1;
        Cache::forever($key, $next);

        return $next;
    }
}
