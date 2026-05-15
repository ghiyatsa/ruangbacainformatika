<?php

namespace App\Jobs;

use App\Models\SimilaritySyncStatus;
use App\Services\SimilarityApiService;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class RemoveSkripsiFromSimilarity implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public readonly int $skripsiId) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function handle(SimilarityApiService $api, SimilaritySyncStatusService $statusService): void
    {
        $statusService->markProcessing($this->skripsiId, SimilaritySyncStatus::OPERATION_DELETE);

        if (! $api->delete($this->skripsiId)) {
            throw new RuntimeException("Gagal menghapus skripsi {$this->skripsiId} dari Similarity API.");
        }

        $statusService->markSynced($this->skripsiId, SimilaritySyncStatus::OPERATION_DELETE);
    }

    public function failed(?Throwable $exception): void
    {
        app(SimilaritySyncStatusService::class)->markFailed(
            $this->skripsiId,
            $exception?->getMessage() ?? 'Penghapusan sinkronisasi gagal tanpa pesan error.',
            SimilaritySyncStatus::OPERATION_DELETE,
        );
    }
}
