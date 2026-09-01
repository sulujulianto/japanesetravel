<?php

namespace App\Support;

final class Brand
{
    public static function name(): string
    {
        return self::stringConfig('brand.name', 'Japan Travel');
    }

    public static function mark(): string
    {
        return self::stringConfig('brand.mark', 'JT');
    }

    public static function legalName(): string
    {
        return self::stringConfig('brand.legal_name', self::name());
    }

    public static function region(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $regions = config('brand.region', []);

        if (! is_array($regions)) {
            return $locale === 'en' ? 'Japan' : 'Jepang';
        }

        $fallbackLocale = config('app.fallback_locale', 'en');
        if (! is_string($fallbackLocale)) {
            $fallbackLocale = 'en';
        }

        $region = $regions[$locale] ?? $regions[$fallbackLocale] ?? null;

        return is_string($region) && $region !== ''
            ? $region
            : ($locale === 'en' ? 'Japan' : 'Jepang');
    }

    /** @return array{name: string, mark: string, legalName: string, region: string} */
    public static function props(): array
    {
        return [
            'name' => self::name(),
            'mark' => self::mark(),
            'legalName' => self::legalName(),
            'region' => self::region(),
        ];
    }

    private static function stringConfig(string $key, string $fallback): string
    {
        $value = config($key, $fallback);

        return is_string($value) && $value !== '' ? $value : $fallback;
    }
}
