<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearSimilarityCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'similarity:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset cache history untuk pengecekan kemiripan skripsi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Membersihkan cache history kemiripan skripsi...');

        $prefix = 'similarity_check_';
        $driver = config('cache.default');

        if ($driver === 'database') {
            $table = config('cache.stores.database.table', 'cache');
            $cachePrefix = config('cache.prefix', '');

            $count = DB::table($table)
                ->where('key', 'like', $cachePrefix.$prefix.'%')
                ->delete();

            $this->info("Berhasil menghapus {$count} entri cache dari database.");
        } elseif ($driver === 'file') {
            $this->warn("Driver cache 'file' tidak mendukung penghapusan berdasarkan prefix secara efisien.");
            $this->warn("Silakan gunakan 'php artisan cache:clear' jika diperlukan.");

            return 1;
        } else {
            // Redis/Memcached might support tags if we used them, but we didn't.
            // For now, we only support direct DB deletion as it's the current driver.
            $this->warn("Driver cache '{$driver}' tidak mendukung penghapusan berdasarkan prefix secara otomatis melalui perintah ini.");
            $this->warn("Silakan gunakan 'php artisan cache:clear' untuk menghapus semua cache.");

            return 1;
        }

        return 0;
    }
}
