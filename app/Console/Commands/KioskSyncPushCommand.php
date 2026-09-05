<?php

namespace App\Console\Commands;

use App\Services\KioskEdgeSyncService;
use Illuminate\Console\Command;

class KioskSyncPushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kiosk:sync-push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unggah transaksi offline (Buku Tamu & Peminjaman) dari PC Kiosk lokal ke server Cloud';

    /**
     * Execute the console command.
     */
    public function handle(KioskEdgeSyncService $syncService): int
    {
        $this->info('Memulai pengunggahan transaksi offline ke Cloud...');

        $result = $syncService->pushTransactionsToCloud();

        if (! $result['success']) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        $this->info($result['message']);
        $this->line("Kunjungan diunggah: {$result['visits_synced']}");
        $this->line("Peminjaman diunggah: {$result['loans_synced']}");

        return self::SUCCESS;
    }
}
