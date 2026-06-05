<?php

namespace App\Support;

use Illuminate\Support\Str;

class Isbn
{
    public static function normalize(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $normalized = Str::of($value)
            ->trim()
            ->upper()
            ->replaceMatches('/[\s-]+/', '')
            ->toString();

        return $normalized !== '' ? $normalized : null;
    }

    public static function isValid(?string $value): bool
    {
        $normalized = static::normalize($value);

        if ($normalized === null) {
            return false;
        }

        return static::isValidNormalized($normalized);
    }

    public static function isValidNormalized(string $value): bool
    {
        if (preg_match('/^\d{13}$/', $value) === 1) {
            return static::passesIsbn13Checksum($value);
        }

        if (preg_match('/^\d{9}[\dX]$/', $value) === 1) {
            return static::passesIsbn10Checksum($value);
        }

        return false;
    }

    protected static function passesIsbn10Checksum(string $value): bool
    {
        $sum = 0;

        foreach (str_split(substr($value, 0, 9)) as $index => $digit) {
            $sum += (10 - $index) * (int) $digit;
        }

        $checkDigit = substr($value, -1);
        $sum += $checkDigit === 'X' ? 10 : (int) $checkDigit;

        return $sum % 11 === 0;
    }

    protected static function passesIsbn13Checksum(string $value): bool
    {
        $sum = 0;

        foreach (str_split(substr($value, 0, 12)) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === (int) substr($value, -1);
    }
}
