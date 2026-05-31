<?php

namespace App\Console\Commands;

use App\Models\KioskDevice;
use App\Models\LoanDraft;
use App\Models\ReturnDraft;
use Carbon\CarbonInterface;
use Illuminate\Console\Attributes\AsCommand;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;

#[AsCommand(name: 'app:prune-temporary-records')]
#[Description('Prune stale temporary drafts and inactive kiosk device sessions')]
class PruneTemporaryRecordsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $draftRetentionDays = max((int) $this->option('draft-days'), 1);
        $deviceRetentionDays = max((int) $this->option('device-days'), 1);

        $draftCutoff = now()->subDays($draftRetentionDays);
        $deviceCutoff = now()->subDays($deviceRetentionDays);

        $prunedLoanDrafts = $this->pruneLoanDrafts($draftCutoff);
        $prunedReturnDrafts = $this->pruneReturnDrafts($draftCutoff);
        $prunedKioskDevices = $this->pruneKioskDevices($deviceCutoff);

        $this->info("Pruned {$prunedLoanDrafts} loan drafts.");
        $this->info("Pruned {$prunedReturnDrafts} return drafts.");
        $this->info("Pruned {$prunedKioskDevices} kiosk devices.");

        return self::SUCCESS;
    }

    protected $signature = 'app:prune-temporary-records
        {--draft-days=7 : Retain expired or consumed draft records for this many days}
        {--device-days=30 : Retain inactive kiosk device sessions for this many days}';

    protected function pruneLoanDrafts(CarbonInterface $cutoff): int
    {
        return LoanDraft::query()
            ->where(function ($query) use ($cutoff): void {
                $query
                    ->where(function ($statusQuery) use ($cutoff): void {
                        $statusQuery
                            ->where('status', LoanDraft::STATUS_CONSUMED)
                            ->where(function ($dateQuery) use ($cutoff): void {
                                $dateQuery
                                    ->where('consumed_at', '<=', $cutoff)
                                    ->orWhere(function ($fallbackQuery) use ($cutoff): void {
                                        $fallbackQuery
                                            ->whereNull('consumed_at')
                                            ->where('updated_at', '<=', $cutoff);
                                    });
                            });
                    })
                    ->orWhere(function ($statusQuery) use ($cutoff): void {
                        $statusQuery
                            ->where('status', LoanDraft::STATUS_EXPIRED)
                            ->where(function ($dateQuery) use ($cutoff): void {
                                $dateQuery
                                    ->where('expires_at', '<=', $cutoff)
                                    ->orWhere(function ($fallbackQuery) use ($cutoff): void {
                                        $fallbackQuery
                                            ->whereNull('expires_at')
                                            ->where('updated_at', '<=', $cutoff);
                                    });
                            });
                    })
                    ->orWhere(function ($statusQuery) use ($cutoff): void {
                        $statusQuery
                            ->where('status', LoanDraft::STATUS_PENDING)
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<=', $cutoff);
                    });
            })
            ->delete();
    }

    protected function pruneReturnDrafts(CarbonInterface $cutoff): int
    {
        return ReturnDraft::query()
            ->where(function ($query) use ($cutoff): void {
                $query
                    ->where(function ($statusQuery) use ($cutoff): void {
                        $statusQuery
                            ->where('status', ReturnDraft::STATUS_CONSUMED)
                            ->where(function ($dateQuery) use ($cutoff): void {
                                $dateQuery
                                    ->where('consumed_at', '<=', $cutoff)
                                    ->orWhere(function ($fallbackQuery) use ($cutoff): void {
                                        $fallbackQuery
                                            ->whereNull('consumed_at')
                                            ->where('updated_at', '<=', $cutoff);
                                    });
                            });
                    })
                    ->orWhere(function ($statusQuery) use ($cutoff): void {
                        $statusQuery
                            ->where('status', ReturnDraft::STATUS_EXPIRED)
                            ->where(function ($dateQuery) use ($cutoff): void {
                                $dateQuery
                                    ->where('expires_at', '<=', $cutoff)
                                    ->orWhere(function ($fallbackQuery) use ($cutoff): void {
                                        $fallbackQuery
                                            ->whereNull('expires_at')
                                            ->where('updated_at', '<=', $cutoff);
                                    });
                            });
                    })
                    ->orWhere(function ($statusQuery) use ($cutoff): void {
                        $statusQuery
                            ->where('status', ReturnDraft::STATUS_PENDING)
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<=', $cutoff);
                    });
            })
            ->delete();
    }

    protected function pruneKioskDevices(CarbonInterface $cutoff): int
    {
        return KioskDevice::query()
            ->whereNotNull('last_active_at')
            ->where('last_active_at', '<=', $cutoff)
            ->delete();
    }
}
