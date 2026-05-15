<?php

namespace App\Observers;

use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilaritySyncDispatcher;
use App\Services\SimilaritySyncStatusService;

class SkripsiObserver
{
    public function created(Skripsi $skripsi): void
    {
        app(SimilaritySyncStatusService::class)->markQueued($skripsi);
        app(SimilaritySyncDispatcher::class)->dispatchUpsert($skripsi->getKey());
    }

    public function updated(Skripsi $skripsi): void
    {
        app(SimilaritySyncStatusService::class)->markQueued($skripsi);
        app(SimilaritySyncDispatcher::class)->dispatchUpsert($skripsi->getKey());
    }

    public function deleted(Skripsi $skripsi): void
    {
        app(SimilaritySyncStatusService::class)->markQueued($skripsi, SimilaritySyncStatus::OPERATION_DELETE);
        app(SimilaritySyncDispatcher::class)->dispatchDelete($skripsi->getKey());
    }
}
