<?php

namespace App\Jobs;

use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class SyncSkripsiChunkToSimilarity implements ShouldQueue, ShouldQueueAfterCommit
{
    use Batchable;
    use Queueable;

    public int $tries = 1;

    public int $timeout = 55;

    public bool $failOnTimeout = true;

    /**
     * @param  array<int, int>  $skripsiIds
     */
    public function __construct(
        public readonly array $skripsiIds,
        public readonly bool $resetIndex = false,
    ) {}

    public function handle(SimilarityApiService $api, SimilaritySyncStatusService $statusService): void
    {
        $skripsis = Skripsi::query()
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

        if ($skripsis->isEmpty()) {
            return;
        }

        foreach ($skripsis as $skripsi) {
            $statusService->markProcessing($skripsi->id);
        }

        $items = $skripsis->map(fn (Skripsi $skripsi): array => [
            'skripsi_id' => $skripsi->id,
            'judul' => $skripsi->title,
            'abstrak' => $skripsi->abstract,
            'kata_kunci' => $skripsi->keywords,
            'tahun' => $skripsi->year,
            'program_studi' => null,
            'nim' => $skripsi->student_id,
            'nama_mahasiswa' => $skripsi->author_name,
        ])->values()->all();

        if ($api->bulkUpsert($items, $this->resetIndex)) {
            foreach ($skripsis as $skripsi) {
                $statusService->markSynced($skripsi->id);
            }

            return;
        }

        foreach ($skripsis as $skripsi) {
            $statusService->markFailed(
                $skripsi->id,
                'Bulk sinkronisasi ke Similarity API gagal.',
            );
        }

        throw new RuntimeException('Bulk sinkronisasi ke Similarity API gagal.');
    }
}
