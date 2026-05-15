<?php

namespace App\Console\Commands;

use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use App\Services\SimilaritySyncStatusService;
use Illuminate\Console\Command;

class SyncSkripsiCommand extends Command
{
    protected $signature = 'skripsi:sync {--chunk=100 : Jumlah per batch} {--reset : Reset data Python dan vector index sebelum sync penuh}';

    protected $description = 'Sinkronisasi seluruh data skripsi ke Similarity API';

    public function __construct(
        private readonly SimilarityApiService $api,
        private readonly SimilaritySyncStatusService $statusService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->api->isHealthy()) {
            $this->error('Similarity API tidak dapat dijangkau.');

            return self::FAILURE;
        }

        $chunk = (int) $this->option('chunk');
        $reset = (bool) $this->option('reset');
        $total = Skripsi::count();
        $mode = $reset ? 'reset + rebuild' : 'upsert bertahap';

        $this->info("Memulai sinkronisasi {$total} skripsi (batch size: {$chunk}, mode: {$mode}) ...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        Skripsi::select([
            'id',
            'title',
            'abstract',
            'keywords',
            'year',
            'student_id',
            'author_name',
        ])->chunkById($chunk, function ($skripsis) use ($bar, &$success, &$failed, $reset) {
            foreach ($skripsis as $skripsi) {
                $this->statusService->markProcessing($skripsi->id);
            }

            $items = $skripsis->map(fn ($s) => [
                'skripsi_id' => $s->id,
                'judul' => $s->title,
                'abstrak' => $s->abstract,
                'kata_kunci' => $s->keywords,
                'tahun' => $s->year,
                'program_studi' => null,
                'nim' => $s->student_id,
                'nama_mahasiswa' => $s->author_name,
            ])->values()->toArray();

            $shouldResetThisBatch = $reset && $success === 0 && $failed === 0;

            if ($this->api->bulkUpsert($items, $shouldResetThisBatch)) {
                foreach ($skripsis as $skripsi) {
                    $this->statusService->markSynced($skripsi->id);
                }

                $success += count($items);
            } else {
                foreach ($skripsis as $skripsi) {
                    $this->statusService->markFailed(
                        $skripsi->id,
                        'Bulk sinkronisasi ke Similarity API gagal.',
                    );
                }

                $failed += count($items);
            }

            $bar->advance(count($items));
        });

        $bar->finish();

        $this->newLine(2);
        $this->info("Berhasil: {$success} | Gagal: {$failed}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
