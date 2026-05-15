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
        $statusService->markProcessing($this->skripsiId);

        $skripsi = Skripsi::query()->find($this->skripsiId);

        if (! $skripsi) {
            $statusService->markFailed($this->skripsiId, 'Data skripsi tidak lagi tersedia untuk disinkronkan.');

            return;
        }

        $synced = $api->upsert([
            'skripsi_id' => $skripsi->id,
            'judul' => $skripsi->title,
            'abstrak' => $skripsi->abstract,
            'kata_kunci' => $skripsi->keywords,
            'tahun' => $skripsi->year,
            'program_studi' => 'Teknik Informatika',
            'nim' => $skripsi->student_id,
            'nama_mahasiswa' => $skripsi->author_name,
        ]);

        if (! $synced) {
            throw new RuntimeException("Gagal menyinkronkan skripsi {$this->skripsiId} ke Similarity API.");
        }

        $statusService->markSynced($this->skripsiId);
    }

    public function failed(?Throwable $exception): void
    {
        app(SimilaritySyncStatusService::class)->markFailed(
            $this->skripsiId,
            $exception?->getMessage() ?? 'Sinkronisasi gagal tanpa pesan error.',
            SimilaritySyncStatus::OPERATION_UPSERT,
        );
    }
}
