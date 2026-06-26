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
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'class', 'style'])
            ->allowElement('figure', ['class', 'data-trix-attachment', 'data-trix-attributes', 'style'])
            ->allowElement('figcaption', ['class', 'style'])
            ->allowRelativeLinks()
            ->allowRelativeMedias();

        if (! app()->isLocal()) {
            $config = $config->forceHttpsUrls();
        }

        $this->sanitizer = new HtmlSanitizer($config);
    }

    /**
     * Sanitize raw HTML content. Null-safe: returns null for null input.
     */
    public function sanitize(mixed $html): ?string
    {
        if ($html === null) {
            return null;
        }

        if (is_array($html)) {
            $resolved = $html['html'] ?? $html['content'] ?? null;
            if ($resolved !== null && ! is_array($resolved)) {
                $html = $resolved;
            } else {
                $html = json_encode($html);
            }
        }

        if (is_object($html)) {
            $html = json_encode($html);
        }

        return $this->sanitizer->sanitize((string) $html);
    }
}
