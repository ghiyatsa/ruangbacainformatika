<?php

namespace App\Console\Commands;

use App\Models\InternshipReport;
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

        if ($reset) {
            $this->statusService->markAllQueuedForFullSync();
        }

        $totalSkripsi = Skripsi::count();
        $totalInternship = InternshipReport::count();
        $total = $totalSkripsi + $totalInternship;
        $mode = $reset ? 'reset + rebuild' : 'upsert bertahap';

        $this->info("Memulai sinkronisasi {$total} dokumen ({$totalSkripsi} skripsi, {$totalInternship} laporan kerja praktek) (batch size: {$chunk}, mode: {$mode}) ...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        // 1. Sync Skripsi
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
                'document_id' => "skripsi_{$s->id}",
                'document_type' => 'skripsi',
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

        // 2. Sync InternshipReport
        InternshipReport::select([
            'id',
            'title',
            'abstract',
            'keywords',
            'year',
            'student_id',
            'author_name',
        ])->chunkById($chunk, function ($internships) use ($bar, &$success, &$failed) {
            $items = $internships->map(fn ($i) => [
                'document_id' => "internship_report_{$i->id}",
                'document_type' => 'internship_report',
                'judul' => $i->title,
                'abstrak' => $i->abstract,
                'kata_kunci' => $i->keywords,
                'tahun' => $i->year,
                'program_studi' => null,
                'nim' => $i->student_id,
                'nama_mahasiswa' => $i->author_name,
            ])->values()->toArray();

            if ($this->api->bulkUpsert($items, false)) {
                $success += count($items);
            } else {
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
