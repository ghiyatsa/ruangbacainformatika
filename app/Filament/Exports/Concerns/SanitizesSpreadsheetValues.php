<?php

declare(strict_types=1);

namespace App\Filament\Exports\Concerns;

use Illuminate\Support\Str;

trait SanitizesSpreadsheetValues
{
    /**
     * Cegah CSV injection: nilai berawalan =, +, -, atau @ diberi prefiks
     * apostrof agar dibaca sebagai teks, bukan dieksekusi sebagai rumus.
     */
    protected static function sanitizeForSpreadsheet(?string $value): string
    {
        $normalizedValue = trim((string) $value);

        if ($normalizedValue === '') {
            return '-';
        }

        return Str::startsWith($normalizedValue, ['=', '+', '-', '@'])
            ? "'{$normalizedValue}"
            : $normalizedValue;
    }
}
