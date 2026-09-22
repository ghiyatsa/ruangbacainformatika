<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\StaticPage;

class StaticPageContent
{
    public function __construct(
        protected RichContentSanitizer $sanitizer,
    ) {}

    /**
     * @return array{summary: string, content: string}
     */
    public function about(): array
    {
        return $this->resolveSystemPage('about');
    }

    /**
     * @return array{summary: string, content: string}
     */
    public function privacyPolicy(): array
    {
        return $this->resolveSystemPage('privacy-policy');
    }

    /**
     * @return array{summary: string, content: string}
     */
    public function termsOfService(): array
    {
        return $this->resolveSystemPage('terms-of-service');
    }

    public function customPage(string $slug): ?StaticPage
    {
        return StaticPage::query()
            ->active()
            ->whereNull('page_key')
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return array{summary: string, content: string}
     */
    protected function resolveSystemPage(string $pageKey): array
    {
        $page = StaticPage::query()
            ->active()
            ->where('page_key', $pageKey)
            ->firstOrFail();

        return $this->present($page);
    }

    /**
     * Samakan perlakuan dengan artikel: konten halaman statis juga dirender
     * lewat dangerouslySetInnerHTML, jadi harus melewati sanitizer yang sama.
     *
     * @return array{summary: string, content: string}
     */
    public function present(StaticPage $page): array
    {
        return [
            'summary' => trim((string) $page->summary),
            'content' => (string) $this->sanitizer->sanitize(trim((string) $page->content)),
        ];
    }
}
