<?php

namespace App\Console\Commands;

use App\Models\KioskDevice;
use Carbon\CarbonInterface;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:prune-temporary-records')]
#[Description('Prune inactive kiosk device sessions')]
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

        $this->info("Pruned {$prunedKioskDevices} kiosk devices.");

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
}
