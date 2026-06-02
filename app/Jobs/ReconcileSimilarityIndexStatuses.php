<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SimilarityIndexReconciliationService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ReconcileSimilarityIndexStatuses implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $initiatedByUserId,
        public readonly int $pageSize = 500,
    ) {}

    public function handle(SimilarityIndexReconciliationService $reconciliationService): void
    {
        $summary = $reconciliationService->reconcile($this->pageSize);
        $user = User::query()->find($this->initiatedByUserId);

        if ($user === null) {
            return;
        }

        $finishedWithIssues = $summary['missing_count'] > 0 || $summary['orphan_index_count'] > 0;

        Notification::make()
            ->title($finishedWithIssues ? 'Rekonsiliasi similarity selesai dengan catatan' : 'Rekonsiliasi similarity selesai')
            ->body(
                "Cocok {$summary['matched_count']}, hilang {$summary['missing_count']}, orphan API {$summary['orphan_index_count']}.",
            )
            ->{$finishedWithIssues ? 'warning' : 'success'}()
            ->sendToDatabase($user);
    }

    public function failed(Throwable $exception): void
    {
        $user = User::query()->find($this->initiatedByUserId);

        if ($user === null) {
            return;
        }

        Notification::make()
            ->title('Rekonsiliasi similarity gagal')
            ->body($exception->getMessage())
            ->danger()
            ->sendToDatabase($user);
    }
}
