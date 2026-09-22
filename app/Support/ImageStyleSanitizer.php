<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Memangkas atribut `style` pada elemen gambar menjadi deklarasi ukuran saja.
 *
 * Fitur "resizable images" Filament menyimpan ukuran sebagai atribut
 * `width`/`height`, namun konten lama atau penyuntingan manual bisa memuat
 * `style="width: ...; height: ..."`. Sanitizer ini mempertahankan deklarasi
 * ukuran tersebut sekaligus membuang properti berisiko seperti `position`,
 * `background-image`, atau `behavior`.
 */
final class ImageStyleSanitizer implements AttributeSanitizerInterface
{
    /** @var list<string> */
    private const ALLOWED_PROPERTIES = ['width', 'height', 'max-width', 'max-height'];

    /**
     * @return list<string>
     */
    public function getSupportedElements(): array
    {
        return ['img', 'figure', 'figcaption'];
    }

    /**
     * @return list<string>
     */
    public function getSupportedAttributes(): array
    {
        return ['style'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $kept = [];

        foreach (explode(';', $value) as $declaration) {
            $parts = explode(':', $declaration, 2);

            if (count($parts) !== 2) {
                continue;
            }

            $property = strtolower(trim($parts[0]));
            $candidate = trim($parts[1]);

            if (! in_array($property, self::ALLOWED_PROPERTIES, true)) {
                continue;
            }

            // Hanya izinkan nilai ukuran sederhana (angka + satuan panjang,
            // persentase, atau `auto`) — tolak `url(...)`, `expression(...)`, dll.
            if (! preg_match('/^(?:auto|\d+(?:\.\d+)?(?:px|%|em|rem|vh|vw|pt|cm|mm|in)?)$/i', $candidate)) {
                continue;
            }

            $kept[] = $property.': '.strtolower($candidate);
        }

        return $kept === [] ? null : implode('; ', $kept);
    }
}
