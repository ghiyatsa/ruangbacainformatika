<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class AppTimezone
{
    public const DISPLAY_TIMEZONE = 'Asia/Jakarta';

    public static function displayTimezone(): string
    {
        return self::DISPLAY_TIMEZONE;
    }

    public static function now(): Carbon
    {
        return Carbon::now(self::displayTimezone());
    }

    public static function toDisplay(?CarbonInterface $dateTime): ?CarbonInterface
    {
        return $dateTime?->copy()->setTimezone(self::displayTimezone());
    }

    public static function format(?CarbonInterface $dateTime, string $format = 'd M Y H:i', string $fallback = '-'): string
    {
        return self::toDisplay($dateTime)?->translatedFormat($format) ?? $fallback;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function dayRange(\DateTimeInterface|string|null $date = null): array
    {
        $day = match (true) {
            $date instanceof \DateTimeInterface => Carbon::instance($date)->setTimezone(self::displayTimezone()),
            is_string($date) && filled($date) => Carbon::parse($date, self::displayTimezone()),
            default => self::now(),
        };

        return [
            $day->copy()->startOfDay()->utc(),
            $day->copy()->endOfDay()->utc(),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function monthRange(?\DateTimeInterface $date = null): array
    {
        $month = $date instanceof \DateTimeInterface
            ? Carbon::instance($date)->setTimezone(self::displayTimezone())
            : self::now();

        return [
            $month->copy()->startOfMonth()->utc(),
            $month->copy()->endOfMonth()->utc(),
        ];
    }
}
