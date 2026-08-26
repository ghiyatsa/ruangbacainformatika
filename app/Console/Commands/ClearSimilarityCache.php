<?php

namespace App\Console\Commands;

use App\Actions\Similarity\CheckSimilarity;
use Illuminate\Console\Command;

class ClearSimilarityCache extends Command
{
    protected $signature = 'similarity:clear-cache';

    protected $description = 'Reset cache hasil pengecekan kemiripan skripsi';

    public function handle(): int
    {
        $version = CheckSimilarity::invalidateCache();

        $this->info("Cache kemiripan di-invalidate (versi cache: {$version}).");

        return self::SUCCESS;
    }
}
