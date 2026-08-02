<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\VisitLog;
use Illuminate\Console\Command;

class PruneAuditLogsCommand extends Command
{
    protected $signature = 'app:prune-audit-logs {--retention-days=180 : Hapus log aktivitas & kunjungan yang lebih tua dari N hari}';

    protected $description = 'Hapus log aktivitas dan kunjungan yang sudah lama.';

    public function handle(): int
    {
        $retentionDays = max((int) $this->option('retention-days'), 0);
        $cutoff = now()->subDays($retentionDays);

        $prunedActivityLogs = ActivityLog::query()
            ->where('created_at', '<=', $cutoff)
            ->delete();
        $prunedVisitLogs = VisitLog::query()
            ->where('created_at', '<=', $cutoff)
            ->delete();

        $this->info("Berhasil menghapus {$prunedActivityLogs} log aktivitas dan {$prunedVisitLogs} log kunjungan yang sudah lama.");

        return self::SUCCESS;
    }
}
