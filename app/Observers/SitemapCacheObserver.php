<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class SitemapCacheObserver
{
    public function saved(mixed $model): void
    {
        Cache::forget('sitemap:xml');
    }

    public function deleted(mixed $model): void
    {
        Cache::forget('sitemap:xml');
    }
}
