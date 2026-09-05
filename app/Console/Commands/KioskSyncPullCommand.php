<?php

namespace App\Console\Commands;

use App\Services\KioskEdgeSyncService;
use Illuminate\Console\Command;

class KioskSyncPullCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kiosk:sync-pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tarik data katalog buku dan anggota aktif dari server Cloud ke PC Kiosk lokal';

    /**
     * Execute the console command.
     */
    public function handle(KioskEdgeSyncService $syncService): int
    {
        $this->info('Memulai penarikan data katalog dari Cloud...');

        $result = $syncService->pullCatalogFromCloud();

        if (! $result['success']) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        $this->info($result['message']);
        $this->line("Buku disinkronkan: {$result['books_synced']}");
        $this->line("User disinkronkan: {$result['users_synced']}");

        return self::SUCCESS;
    }
}
