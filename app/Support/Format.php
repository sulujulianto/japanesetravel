<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use NumberFormatter;
use Throwable;

final class Format
{
    private const DEFAULT_LOCALE = 'en';

    private const EMPTY_DATE = '—';

    public static function locale(?string $locale = null): string
    {
        $candidate = $locale ?? app()->getLocale();
        $normalized = self::normalizeLocale($candidate);

        if ($normalized !== null) {
            return $normalized;
        }

        return self::normalizeLocale((string) config('app.fallback_locale'))
            ?? self::DEFAULT_LOCALE;
    }

    public static function jsLocale(?string $locale = null): string
    {
        return self::locale($locale) === 'id' ? 'id-ID' : 'en-US';
    }

    public static function number(
        int|float|string|null $value,
        int $decimals = 0,
        ?string $locale = null
    ): string {
        $numericValue = self::numericValue($value);
        $precision = max(0, $decimals);
        $resolvedLocale = self::locale($locale);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter(
                $resolvedLocale === 'id' ? 'id_ID' : 'en_US',
                NumberFormatter::DECIMAL
            );
            $formatter->setAttribute(NumberFormatter::GROUPING_USED, 1);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $precision);
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $precision);
            $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFUP);

            $formatted = $formatter->format($numericValue);

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return number_format(
            $numericValue,
            $precision,
            $resolvedLocale === 'id' ? ',' : '.',
            $resolvedLocale === 'id' ? '.' : ','
        );
    }

    public static function idr(int|float|string|null $amount, ?string $locale = null): string
    {
        $resolvedLocale = self::locale($locale);
        $formatted = self::number($amount, 0, $resolvedLocale);

        return $resolvedLocale === 'id' ? 'Rp'.$formatted : 'IDR '.$formatted;
    }

    public static function rating(int|float|string|null $value, ?string $locale = null): string
    {
        return self::number($value, 1, $locale);
    }

    public static function date(DateTimeInterface|string|null $date, ?string $locale = null): string
    {
        $resolvedLocale = self::locale($locale);
        $parsed = self::dateValue($date);

        if ($parsed === null) {
            return self::EMPTY_DATE;
        }

        return $parsed
            ->locale($resolvedLocale)
            ->isoFormat($resolvedLocale === 'id' ? 'D MMM YYYY' : 'MMM D, YYYY');
    }

    public static function dateTime(DateTimeInterface|string|null $date, ?string $locale = null): string
    {
        $resolvedLocale = self::locale($locale);
        $parsed = self::dateValue($date);

        if ($parsed === null) {
            return self::EMPTY_DATE;
        }

        return $parsed
            ->locale($resolvedLocale)
            ->isoFormat($resolvedLocale === 'id' ? 'D MMM YYYY, HH.mm' : 'MMM D, YYYY, h:mm A');
    }

    public static function relative(DateTimeInterface|string|null $date, ?string $locale = null): string
    {
        $resolvedLocale = self::locale($locale);
        $parsed = self::dateValue($date);

        if ($parsed === null) {
            return self::EMPTY_DATE;
        }

        return $parsed->locale($resolvedLocale)->diffForHumans();
    }

    private static function normalizeLocale(string $locale): ?string
    {
        $language = strtolower(explode('-', str_replace('_', '-', trim($locale)))[0]);

        return in_array($language, ['id', 'en'], true) ? $language : null;
    }

    private static function numericValue(int|float|string|null $value): int|float
    {
        if ($value === null || ! is_numeric($value)) {
            return 0;
        }

        return $value + 0;
    }

    private static function dateValue(DateTimeInterface|string|null $date): ?CarbonImmutable
    {
        if ($date === null) {
            return null;
        }

        try {
            return $date instanceof DateTimeInterface
                ? CarbonImmutable::instance($date)
                : CarbonImmutable::parse($date);
        } catch (Throwable) {
            return null;
        }
    }
}
