<?php

namespace App\Support;

use Illuminate\Validation\Rule;

final class PlaceSchedule
{
    /** @var array<string, string> */
    private const DAY_LABELS = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ];

    /** @var list<string> */
    private const MINUTES = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];

    /** @var list<string> */
    private const PERIODS = ['AM', 'PM'];

    /** @var list<string> */
    private const FIELDS = [
        'open_day_start',
        'open_day_end',
        'open_time_start_hour',
        'open_time_start_minute',
        'open_time_start_period',
        'open_time_end_hour',
        'open_time_end_minute',
        'open_time_end_period',
    ];

    /** @return array<string, list<mixed>> */
    public static function rules(): array
    {
        $requiredWith = 'required_with:'.implode(',', self::FIELDS);

        return [
            'open_day_start' => ['nullable', $requiredWith, Rule::in(array_keys(self::DAY_LABELS))],
            'open_day_end' => ['nullable', $requiredWith, Rule::in(array_keys(self::DAY_LABELS))],
            'open_time_start_hour' => ['nullable', $requiredWith, 'integer', 'between:1,12'],
            'open_time_start_minute' => ['nullable', $requiredWith, Rule::in(self::MINUTES)],
            'open_time_start_period' => ['nullable', $requiredWith, Rule::in(self::PERIODS)],
            'open_time_end_hour' => ['nullable', $requiredWith, 'integer', 'between:1,12'],
            'open_time_end_minute' => ['nullable', $requiredWith, Rule::in(self::MINUTES)],
            'open_time_end_period' => ['nullable', $requiredWith, Rule::in(self::PERIODS)],
            'clear_schedule' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, mixed> */
    public static function options(): array
    {
        return [
            'days' => array_map(
                fn (string $value, string $label): array => ['value' => $value, 'label' => __($label)],
                array_keys(self::DAY_LABELS),
                array_values(self::DAY_LABELS),
            ),
            'hours' => range(1, 12),
            'minutes' => self::MINUTES,
            'periods' => self::PERIODS,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>|null
     */
    public static function fromValidated(array $validated): ?array
    {
        if (! isset($validated['open_day_start'])) {
            return null;
        }

        return [
            'version' => 1,
            'dayStart' => (string) $validated['open_day_start'],
            'dayEnd' => (string) $validated['open_day_end'],
            'timeStart' => [
                'hour' => (int) $validated['open_time_start_hour'],
                'minute' => (string) $validated['open_time_start_minute'],
                'period' => (string) $validated['open_time_start_period'],
            ],
            'timeEnd' => [
                'hour' => (int) $validated['open_time_end_hour'],
                'minute' => (string) $validated['open_time_end_minute'],
                'period' => (string) $validated['open_time_end_period'],
            ],
        ];
    }

    /** @return array<string, string> */
    public static function formValues(mixed $schedule): array
    {
        $empty = array_fill_keys(self::FIELDS, '');
        if (! is_array($schedule) || ! self::isValid($schedule)) {
            return $empty;
        }

        /** @var array<string, mixed> $timeStart */
        $timeStart = $schedule['timeStart'];
        /** @var array<string, mixed> $timeEnd */
        $timeEnd = $schedule['timeEnd'];

        return [
            'open_day_start' => (string) $schedule['dayStart'],
            'open_day_end' => (string) $schedule['dayEnd'],
            'open_time_start_hour' => (string) $timeStart['hour'],
            'open_time_start_minute' => (string) $timeStart['minute'],
            'open_time_start_period' => (string) $timeStart['period'],
            'open_time_end_hour' => (string) $timeEnd['hour'],
            'open_time_end_minute' => (string) $timeEnd['minute'],
            'open_time_end_period' => (string) $timeEnd['period'],
        ];
    }

    /**
     * @param  array<string, mixed>  $schedule
     * @return array{open_days: string, open_hours: string, opening_hours: array<string, mixed>}
     */
    public static function attributes(array $schedule): array
    {
        /** @var array<string, mixed> $timeStart */
        $timeStart = $schedule['timeStart'];
        /** @var array<string, mixed> $timeEnd */
        $timeEnd = $schedule['timeEnd'];

        $dayStart = (string) $schedule['dayStart'];
        $dayEnd = (string) $schedule['dayEnd'];

        return [
            'open_days' => (self::DAY_LABELS[$dayStart] ?? $dayStart).' - '.(self::DAY_LABELS[$dayEnd] ?? $dayEnd),
            'open_hours' => self::formatTime($timeStart).' - '.self::formatTime($timeEnd),
            'opening_hours' => $schedule,
        ];
    }

    /** @param array<string, mixed> $schedule */
    private static function isValid(array $schedule): bool
    {
        $timeStart = $schedule['timeStart'] ?? null;
        $timeEnd = $schedule['timeEnd'] ?? null;

        return isset(self::DAY_LABELS[(string) ($schedule['dayStart'] ?? '')])
            && isset(self::DAY_LABELS[(string) ($schedule['dayEnd'] ?? '')])
            && is_array($timeStart)
            && is_array($timeEnd)
            && self::isValidTime($timeStart)
            && self::isValidTime($timeEnd);
    }

    /** @param array<string, mixed> $time */
    private static function isValidTime(array $time): bool
    {
        $hour = filter_var($time['hour'] ?? null, FILTER_VALIDATE_INT);

        return is_int($hour)
            && $hour >= 1
            && $hour <= 12
            && in_array((string) ($time['minute'] ?? ''), self::MINUTES, true)
            && in_array((string) ($time['period'] ?? ''), self::PERIODS, true);
    }

    /** @param array<string, mixed> $time */
    private static function formatTime(array $time): string
    {
        return sprintf('%02d:%s %s', (int) $time['hour'], (string) $time['minute'], (string) $time['period']);
    }
}
