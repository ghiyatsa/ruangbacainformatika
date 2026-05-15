<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class RunFullSimilaritySync implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    public bool $failOnTimeout = true;

    public function __construct(public readonly int $chunk = 100) {}

    public function handle(): void
    {
        Artisan::call(sprintf('skripsi:sync --chunk=%d --reset', $this->chunk));
    }
}
