<?php

namespace App\Jobs;

use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
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

    public function __construct(
        public readonly int $skripsiId,
        public readonly string $modelClass = Skripsi::class,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function handle(SimilarityApiService $api, SimilaritySyncStatusService $statusService): void
    {
        $statusService->markProcessing($this->skripsiId, SimilaritySyncStatus::OPERATION_DELETE, $this->modelClass);

        $documentType = $this->modelClass === Skripsi::class ? 'skripsi' : 'internship_report';
        $documentId = "{$documentType}_{$this->skripsiId}";

        if (! $api->delete($documentId)) {
            throw new RuntimeException("Gagal menghapus dokumen {$documentId} dari Similarity API.");
        }

        $statusService->deleteStatus($this->skripsiId, $this->modelClass);
    }

    public function failed(?Throwable $exception): void
    {
        app(SimilaritySyncStatusService::class)->markFailed(
            $this->skripsiId,
            $exception?->getMessage() ?? 'Penghapusan sinkronisasi gagal tanpa pesan error.',
            SimilaritySyncStatus::OPERATION_DELETE,
            $this->modelClass,
        );
    }
}
