<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Defense-in-depth sanitizer for rich-text content rendered to the browser.
 *
 * Konten artikel disusun lewat Filament RichEditor dan dirender via
 * React dangerouslySetInnerHTML. Sanitizer ini membersihkan payload berbahaya
 * (script, event handler inline, skema javascript:) sebelum konten dikirim ke
 * frontend, sehingga melindungi data lama maupun baru secara konsisten.
 */
class RichContentSanitizer
{
    protected HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->forceHttpsUrls()
            ->allowRelativeLinks();

        $this->sanitizer = new HtmlSanitizer($config);
    }

    /**
     * Sanitize raw HTML content. Null-safe: returns null for null input.
     */
    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        return $this->sanitizer->sanitize($html);
    }
}
