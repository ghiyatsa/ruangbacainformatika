<?php

namespace App\Jobs;

use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SyncSkripsiChunkToSimilarity implements ShouldQueue, ShouldQueueAfterCommit
{
    use Batchable;
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public bool $failOnTimeout = true;

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120];
    }

    /**
     * @param  array<int, int>  $skripsiIds
     */
    public function __construct(
        public readonly array $skripsiIds,
        public readonly bool $resetIndex = false,
        public readonly string $modelClass = Skripsi::class,
    ) {}

    public function handle(SimilarityApiService $api, SimilaritySyncStatusService $statusService): void
    {
        $records = $this->modelClass::query()
            ->select([
                'id',
                'title',
                'abstract',
                'keywords',
                'year',
                'student_id',
                'author_name',
            ])
            ->whereKey($this->skripsiIds)
            ->orderBy('id')
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        foreach ($records as $record) {
            $statusService->markProcessing($record->id, SimilaritySyncStatus::OPERATION_UPSERT, $this->modelClass);
        }

        $documentType = $this->modelClass === Skripsi::class ? 'skripsi' : 'internship_report';

        $items = $records->map(fn ($record): array => [
            'document_id' => "{$documentType}_{$record->id}",
            'document_type' => $documentType,
            'skripsi_id' => $record->id,
            'judul' => $record->title,
            'abstrak' => $record->abstract,
            'kata_kunci' => $record->keywords,
            'tahun' => $record->year,
            'program_studi' => null,
            'nim' => $record->student_id,
            'nama_mahasiswa' => $record->author_name,
        ])->values()->all();

        try {
            $api->bulkUpsert($items, $this->resetIndex);
        } catch (Throwable $exception) {
            $message = $exception->getMessage() ?: 'Bulk sinkronisasi ke Similarity API gagal.';

            foreach ($records as $record) {
                $statusService->markFailed(
                    $record->id,
                    $message,
                    SimilaritySyncStatus::OPERATION_UPSERT,
                    $this->modelClass,
                );
            }

            throw $exception;
        }

        foreach ($records as $record) {
            $statusService->markSynced($record->id, SimilaritySyncStatus::OPERATION_UPSERT, $this->modelClass);
        }
    }
}
