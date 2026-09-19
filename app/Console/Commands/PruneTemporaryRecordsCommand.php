<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\KioskDevice;
use App\Models\KioskIdempotencyRecord;
use Carbon\CarbonInterface;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:prune-temporary-records')]
#[Description('Prune inactive kiosk device sessions and expired idempotency records')]
class PruneTemporaryRecordsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deviceRetentionDays = max((int) $this->option('device-days'), 1);
        $deviceCutoff = now()->subDays($deviceRetentionDays);

        $prunedKioskDevices = $this->pruneKioskDevices($deviceCutoff);
        $prunedIdempotencyRecords = $this->pruneIdempotencyRecords();

        $this->info("Pruned {$prunedKioskDevices} kiosk devices.");
        $this->info("Pruned {$prunedIdempotencyRecords} idempotency records.");

        return self::SUCCESS;
    }

    protected $signature = 'app:prune-temporary-records
        {--device-days=30 : Retain inactive kiosk device sessions for this many days}';

    protected function pruneKioskDevices(CarbonInterface $cutoff): int
    {
        return KioskDevice::query()
            ->whereNotNull('last_active_at')
            ->where('last_active_at', '<=', $cutoff)
            ->delete();
    }

    /**
     * Buang record idempotency yang masa simpannya sudah lewat. Record tanpa
     * `expires_at` (data lama) ikut dibersihkan agar tabel tidak menumpuk.
     */
    protected function pruneIdempotencyRecords(): int
    {
        return KioskIdempotencyRecord::query()
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '<=', now()))
            ->delete();
    }
}
