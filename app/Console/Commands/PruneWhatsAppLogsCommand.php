<?php

namespace App\Console\Commands;

use App\Models\WhatsAppMessageLog;
use Illuminate\Console\Command;

class PruneWhatsAppLogsCommand extends Command
{
    protected $signature = 'app:prune-whatsapp-logs {--retention-days=90 : Hapus log WhatsApp yang lebih tua dari N hari}';

    protected $description = 'Hapus log pengiriman WhatsApp yang sudah lama.';

    public function handle(): int
    {
        $retentionDays = max((int) $this->option('retention-days'), 0);

        $pruned = WhatsAppMessageLog::query()
            ->where('created_at', '<=', now()->subDays($retentionDays))
            ->delete();

        $this->info("Berhasil menghapus {$pruned} log WhatsApp yang sudah lama.");

        return self::SUCCESS;
    }
}
