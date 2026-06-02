<?php

namespace App\Services;

use App\Jobs\SyncSkripsiChunkToSimilarity;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Throwable;

class SimilarityFullSyncDispatcher
{
    private const DEFAULT_CHUNK_SIZE = 100;

    public function __construct(
        private readonly SimilaritySyncStatusService $statusService,
    ) {}

    /**
     * @return array{mode: 'sync'|'queued', success: bool, error_message?: string|null, batch_id?: string|null}
     */
    public function dispatch(int $chunk = self::DEFAULT_CHUNK_SIZE, bool $forceSync = false, ?int $initiatedByUserId = null): array
    {
        try {
            $command = sprintf('skripsi:sync --chunk=%d --reset', $chunk);

            if ($forceSync || $this->shouldRunSynchronously()) {
                $this->statusService->markAllQueuedForFullSync();
                $exitCode = Artisan::call($command);

                return [
                    'mode' => 'sync',
                    'success' => $exitCode === 0,
                    'error_message' => $exitCode === 0 ? null : 'Sinkronisasi penuh berhenti sebelum semua data selesai diproses.',
                    'batch_id' => null,
                ];
            }

            $this->statusService->markAllQueuedForFullSync();
            $jobs = $this->buildChunkJobs($chunk);

            if ($jobs === []) {
                $this->notifyCompletion($initiatedByUserId);

                return [
                    'mode' => 'queued',
                    'success' => true,
                    'error_message' => null,
                    'batch_id' => null,
                ];
            }

            $batch = Bus::batch($jobs)
                ->name('similarity-full-sync')
                ->allowFailures()
                ->finally(function (Batch $batch) use ($initiatedByUserId): void {
                    app(self::class)->notifyCompletion($initiatedByUserId, $batch);
                })
                ->dispatch();

            return [
                'mode' => 'queued',
                'success' => true,
                'error_message' => null,
                'batch_id' => $batch->id,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'mode' => $forceSync ? 'sync' : 'queued',
                'success' => false,
                'error_message' => $exception->getMessage(),
                'batch_id' => null,
            ];
        }
    }

    private function shouldRunSynchronously(): bool
    {
        return in_array(config('queue.default'), ['background', 'deferred', 'sync'], true);
    }

    /**
     * @return array<int, SyncSkripsiChunkToSimilarity>
     */
    private function buildChunkJobs(int $chunk): array
    {
        $jobs = [];
        $shouldResetIndex = true;

        Skripsi::query()
            ->select('id')
            ->orderBy('id')
            ->chunkById($chunk, function ($skripsis) use (&$jobs, &$shouldResetIndex): void {
                $jobs[] = new SyncSkripsiChunkToSimilarity(
                    skripsiIds: $skripsis->pluck('id')->all(),
                    resetIndex: $shouldResetIndex,
                );

                $shouldResetIndex = false;
            });

        return $jobs;
    }

    private function notifyCompletion(?int $initiatedByUserId, ?Batch $batch = null): void
    {
        if ($initiatedByUserId === null) {
            return;
        }

        $user = User::query()->find($initiatedByUserId);

        if ($user === null) {
            return;
        }

        $syncedCount = SimilaritySyncStatus::query()
            ->forExistingSkripsi()
            ->where('status', SimilaritySyncStatus::STATUS_SYNCED)
            ->count();
        $failedCount = SimilaritySyncStatus::query()
            ->forExistingSkripsi()
            ->where('status', SimilaritySyncStatus::STATUS_FAILED)
            ->count();
        $pendingCount = SimilaritySyncStatus::query()
            ->forExistingSkripsi()
            ->whereIn('status', [
                SimilaritySyncStatus::STATUS_PENDING,
                SimilaritySyncStatus::STATUS_SYNCING,
            ])
            ->count();

        $finishedWithIssues = $failedCount > 0 || $pendingCount > 0 || (($batch?->failedJobs ?? 0) > 0);

        Notification::make()
            ->title($finishedWithIssues ? 'Sinkronisasi similarity selesai dengan catatan' : 'Sinkronisasi similarity selesai')
            ->body("Berhasil {$syncedCount}, gagal {$failedCount}, menunggu {$pendingCount}.")
            ->{$finishedWithIssues ? 'warning' : 'success'}()
            ->sendToDatabase($user);
    }
}
