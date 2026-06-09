<?php

namespace Tests\Unit\Support;

use App\Support\Format;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class FormatTest extends TestCase
{
    public function test_it_formats_idr_for_supported_locales(): void
    {
        $this->assertSame('Rp1.234.567', Format::idr(1234567, 'id'));
        $this->assertSame('IDR 1,234,567', Format::idr(1234567, 'en'));
    }

    public function test_it_formats_numbers_for_supported_locales(): void
    {
        $this->assertSame('1.234.567', Format::number(1234567, 0, 'id'));
        $this->assertSame('1,234,567', Format::number(1234567, 0, 'en'));
        $this->assertSame('-1.234,5', Format::number(-1234.5, 1, 'id'));
        $this->assertSame('-1,234.5', Format::number(-1234.5, 1, 'en'));
    }

    public function test_it_formats_ratings_for_supported_locales(): void
    {
        $this->assertSame('4,5', Format::rating(4.5, 'id'));
        $this->assertSame('4.5', Format::rating(4.5, 'en'));
    }

    public function test_it_formats_dates_for_supported_locales(): void
    {
        $this->assertSame('9 Jun 2026', Format::date('2026-06-09', 'id'));
        $this->assertSame('Jun 9, 2026', Format::date('2026-06-09', 'en'));
    }

    public function test_it_formats_date_times_without_changing_timezone(): void
    {
        $date = CarbonImmutable::parse('2026-06-09 14:30:00', 'Asia/Jakarta');

        $this->assertSame('9 Jun 2026, 14.30', Format::dateTime($date, 'id'));
        $this->assertSame('Jun 9, 2026, 2:30 PM', Format::dateTime($date, 'en'));
        $this->assertSame('Asia/Jakarta', $date->timezoneName);
    }

    public function test_it_maps_locales_for_javascript(): void
    {
        $this->assertSame('id-ID', Format::jsLocale('id'));
        $this->assertSame('en-US', Format::jsLocale('en'));
    }

    public function test_it_uses_the_application_locale_when_none_is_provided(): void
    {
        app()->setLocale('id');

        $this->assertSame('id', Format::locale());
        $this->assertSame('id-ID', Format::jsLocale());
        $this->assertSame('Rp1.000', Format::idr(1000));
    }

    public function test_it_uses_the_configured_fallback_for_unknown_locales(): void
    {
        config()->set('app.fallback_locale', 'id');

        $this->assertSame('id', Format::locale('ja'));
        $this->assertSame('id-ID', Format::jsLocale('ja'));

        config()->set('app.fallback_locale', 'ja');

        $this->assertSame('en', Format::locale('fr'));
    }

    public function test_it_handles_null_and_numeric_strings_deterministically(): void
    {
        $this->assertSame('0', Format::number(null, 0, 'en'));
        $this->assertSame('Rp0', Format::idr(null, 'id'));
        $this->assertSame('0.0', Format::rating(null, 'en'));
        $this->assertSame('1.234,5', Format::number('1234.5', 1, 'id'));
        $this->assertSame('IDR 1,235', Format::idr('1234.5', 'en'));
    }

    public function test_it_returns_a_safe_fallback_for_missing_or_invalid_dates(): void
    {
        $this->assertSame('—', Format::date(null, 'id'));
        $this->assertSame('—', Format::dateTime(null, 'en'));
        $this->assertSame('—', Format::relative(null, 'id'));
        $this->assertSame('—', Format::date('not-a-date', 'en'));
    }

    public function test_it_formats_relative_dates_for_supported_locales(): void
    {
        CarbonImmutable::setTestNow('2026-06-09 14:30:00');
        $date = CarbonImmutable::parse('2026-06-09 12:30:00');

        $this->assertSame('2 jam yang lalu', Format::relative($date, 'id'));
        $this->assertSame('2 hours ago', Format::relative($date, 'en'));

        CarbonImmutable::setTestNow();
    }
}
