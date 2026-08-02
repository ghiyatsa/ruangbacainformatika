<?php

namespace App\Console\Commands;

use App\Models\SearchHistory;
use Illuminate\Console\Command;

class PruneSearchHistoryCommand extends Command
{
    protected $signature = 'app:prune-search-history {--retention-days=90 : Hapus riwayat pencarian yang lebih tua dari N hari}';

    protected $description = 'Hapus riwayat pencarian yang sudah lama.';

    public function handle(): int
    {
        $retentionDays = max((int) $this->option('retention-days'), 0);

        $pruned = SearchHistory::query()
            ->where('created_at', '<=', now()->subDays($retentionDays))
            ->delete();

        $this->info("Berhasil menghapus {$pruned} riwayat pencarian yang sudah lama.");

        return self::SUCCESS;
    }
}
