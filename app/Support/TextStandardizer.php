<?php

namespace App\Support;

use Illuminate\Support\Str;

class TextStandardizer
{
    /**
     * Kata pendek yang umumnya tetap ditulis huruf kapital pada judul bidang informatika.
     *
     * @var array<int, string>
     */
    protected const ACRONYMS = [
        'AI', 'API', 'CRUD', 'CSS', 'DB', 'DNS', 'FTP', 'GUI', 'HTML', 'HTTP', 'HTTPS',
        'IDE', 'IP', 'ISBN', 'ISSN', 'IT', 'JS', 'JSON', 'MVC', 'OOP', 'OS', 'OTP',
        'PHP', 'PT', 'QR', 'REST', 'RFID', 'SDK', 'SQL', 'SSH', 'TCP', 'UI', 'URL', 'UX',
    ];

    public static function squish(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $normalized = Str::of($value)->squish()->trim()->toString();

        return $normalized !== '' ? $normalized : null;
    }

    public static function titleCase(?string $value): ?string
    {
        $squished = static::squish($value);

        if ($squished === null) {
            return null;
        }

        $words = preg_split('/\s+/', $squished);

        return collect($words)
            ->map(fn (string $word): string => static::titleCaseWord($word))
            ->implode(' ');
    }

    protected static function titleCaseWord(string $word): string
    {
        $upper = mb_strtoupper($word);

        if (in_array($upper, self::ACRONYMS, true)) {
            return $upper;
        }

        if ($upper === $word && mb_strlen($word) >= 2) {
            return $word;
        }

        return mb_strtoupper(mb_substr($word, 0, 1)).mb_substr($word, 1);
    }
}
