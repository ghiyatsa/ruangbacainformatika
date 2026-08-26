<?php

namespace App\Observers;

use App\Services\SimilaritySyncDispatcher;

class SimilaritySyncObserver
{
    public function deleted(mixed $model): void
    {
        app(SimilaritySyncDispatcher::class)->dispatchDelete($model->getKey(), $model::class);
    }
}
