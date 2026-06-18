<?php

namespace App\Support;

use App\Models\StaticPage;

class StaticPageContent
{
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

        return [
            'summary' => trim($page->summary),
            'content' => trim($page->content),
        ];
    }
}
