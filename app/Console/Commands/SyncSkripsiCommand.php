<?php

namespace App\Console\Commands;

use App\Models\Skripsi;
use App\Services\SimilarityApiService;
use Illuminate\Console\Command;

class SyncSkripsiCommand extends Command
{
    protected $signature = 'skripsi:sync {--chunk=100 : Jumlah per batch}';

    protected $description = 'Sinkronisasi seluruh data skripsi ke Similarity API';

    public function __construct(
        private readonly SimilarityApiService $api
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->api->isHealthy()) {
            $this->error('❌ Similarity API tidak dapat dijangkau.');

            return self::FAILURE;
        }

        $chunk = (int) $this->option('chunk');
        $total = Skripsi::count();

        $this->info("📤 Sinkronisasi {$total} skripsi (batch size: {$chunk}) ...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        Skripsi::select([
            'id', 'title', 'abstract', 'keywords',
            'year', 'student_id', 'author_name',
        ])
            ->chunkById($chunk, function ($skripsis) use ($bar, &$success, &$failed) {
                $items = $skripsis->map(fn ($s) => [
                    'laravel_id' => $s->id,
                    'judul' => $s->title,
                    'abstrak' => $s->abstract,
                    'kata_kunci' => $s->keywords,
                    'tahun' => $s->year,
                    'program_studi' => null,
                    'nim' => $s->student_id,
                    'nama_mahasiswa' => $s->author_name,
                ])->values()->toArray();

                if ($this->api->bulkUpsert($items)) {
                    $success += count($items);
                } else {
                    $failed += count($items);
                }

                $bar->advance(count($items));
            });

        $bar->finish();

        $this->newLine(2);
        $this->info("✅ Berhasil: {$success}  ❌ Gagal: {$failed}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
