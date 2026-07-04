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

class SyncSkripsiToSimilarity implements ShouldQueue, ShouldQueueAfterCommit
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
        $statusService->markProcessing($this->skripsiId, SimilaritySyncStatus::OPERATION_UPSERT, $this->modelClass);

        $model = $this->modelClass::query()->find($this->skripsiId);

        if (! $model) {
            $statusService->markFailed($this->skripsiId, 'Data tidak lagi tersedia untuk disinkronkan.', SimilaritySyncStatus::OPERATION_UPSERT, $this->modelClass);

            return;
        }

        $documentType = $this->modelClass === Skripsi::class ? 'skripsi' : 'internship_report';
        $documentId = "{$documentType}_{$model->id}";

        $synced = $api->upsert([
            'document_id' => $documentId,
            'document_type' => $documentType,
            'skripsi_id' => $model->id,
            'judul' => $model->title,
            'abstrak' => $model->abstract,
            'kata_kunci' => $model->keywords,
            'tahun' => $model->year,
            'program_studi' => 'Teknik Informatika',
            'nim' => $model->student_id,
            'nama_mahasiswa' => $model->author_name,
        ]);

        if (! $synced) {
            throw new RuntimeException("Gagal menyinkronkan data {$this->modelClass} ID {$this->skripsiId} ke Similarity API.");
        }

        $statusService->markSynced($this->skripsiId, SimilaritySyncStatus::OPERATION_UPSERT, $this->modelClass);
    }

    public function failed(?Throwable $exception): void
    {
        app(SimilaritySyncStatusService::class)->markFailed(
            $this->skripsiId,
            $exception?->getMessage() ?? 'Sinkronisasi gagal tanpa pesan error.',
            SimilaritySyncStatus::OPERATION_UPSERT,
            $this->modelClass,
        );
    }
}
