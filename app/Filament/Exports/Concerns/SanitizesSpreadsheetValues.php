<?php

namespace App\Filament\Exports\Concerns;

use Illuminate\Support\Str;

trait SanitizesSpreadsheetValues
{
    /**
     * Netralkan nilai yang berpotensi menjadi formula spreadsheet (CSV injection).
     *
     * Nilai yang diawali =, +, -, atau @ diberi prefiks apostrof agar dibaca
     * sebagai teks oleh Excel/LibreOffice, bukan dieksekusi sebagai rumus.
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
