<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class PruneNotificationsCommand extends Command
{
    protected $signature = 'app:prune-notifications {--retention-days=7 : Hapus notifikasi yang lebih tua dari N hari}';

    protected $description = 'Hapus notifikasi internal user yang sudah lama.';

    public function handle(): int
    {
        $retentionDays = max((int) $this->option('retention-days'), 0);

        $pruned = DatabaseNotification::query()
            ->where('created_at', '<=', now()->subDays($retentionDays))
            ->delete();

        $this->info("Berhasil menghapus {$pruned} notifikasi yang sudah lama.");

        return self::SUCCESS;
    }
}
