<?php

namespace App\Support;

use Illuminate\Support\Str;

class WhatsAppPhoneNumber
{
    public function normalize(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', trim((string) $value)) ?? '';

        if ($digits === '') {
            return null;
        }

        $subscriberNumber = $digits;

        if (Str::startsWith($subscriberNumber, '62')) {
            $subscriberNumber = substr($subscriberNumber, 2);
        }

        $subscriberNumber = ltrim($subscriberNumber, '0');

        if ($subscriberNumber === '') {
            return null;
        }

        if (Str::startsWith($subscriberNumber, '8')) {
            return '0'.$subscriberNumber;
        }

        return $digits;
    }

    /**
     * @return list<string>
     */
    public function equivalentNumbers(?string $value): array
    {
        $normalized = $this->normalize($value);

        if ($normalized === null) {
            return [];
        }

        if (! Str::startsWith($normalized, '0')) {
            return [$normalized];
        }

        return array_values(array_unique([
            $normalized,
            '62'.substr($normalized, 1),
        ]));
    }
}
