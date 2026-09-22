<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Meloloskan HANYA deklarasi `text-align` dari atribut `style`.
 *
 * Filament RichEditor menyimpan perataan paragraf/heading sebagai inline style
 * (`style="text-align: center"`). Tanpa sanitizer ini atribut `style` dibuang
 * seluruhnya oleh konfigurasi bawaan, sehingga perataan yang dipilih penulis
 * hilang saat artikel dirender.
 *
 * Deklarasi lain (mis. `position`, `background-image`, `url(...)`) tetap
 * dibuang supaya atribut `style` tidak menjadi celah CSS injection.
 *
 * Catatan: elemen gambar (`img`/`figure`/`figcaption`) sengaja TIDAK termasuk
 * dalam daftar elemen yang didukung, agar `style` untuk ukuran gambar hasil
 * fitur "resizable images" tetap utuh.
 */
final class TextAlignStyleSanitizer implements AttributeSanitizerInterface
{
    /** @var list<string> */
    private const ALLOWED_ALIGNMENTS = ['left', 'right', 'center', 'justify', 'start', 'end'];

    /**
     * @return list<string>
     */
    public function getSupportedElements(): array
    {
        return [
            'p', 'div', 'span', 'li', 'td', 'th', 'blockquote',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        ];
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
        $alignment = null;

        foreach (explode(';', $value) as $declaration) {
            $parts = explode(':', $declaration, 2);

            if (count($parts) !== 2) {
                continue;
            }

            $property = strtolower(trim($parts[0]));
            $candidate = strtolower(trim($parts[1]));

            if ($property === 'text-align' && in_array($candidate, self::ALLOWED_ALIGNMENTS, true)) {
                $alignment = $candidate;
            }
        }

        // Tidak ada text-align yang sah: buang seluruh atribut style.
        if ($alignment === null) {
            return null;
        }

        return 'text-align: '.$alignment;
    }
}
