<?php

namespace App\Filament\Widgets;

use App\Models\FailedJob;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class QueueOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '15s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $pendingCount = DB::table('jobs')->count();
        $failedCount = FailedJob::query()->count();
        $activeBatchCount = DB::table('job_batches')
            ->whereNull('finished_at')
            ->count();
        $oldestPending = $this->resolveOldestPendingAge();

        return [
            Stat::make('Job Antre', $pendingCount)
                ->description($pendingCount > 0 ? "Antrean tertua {$oldestPending}" : 'Tidak ada antrean')
                ->descriptionIcon($pendingCount > 0 ? Heroicon::OutlinedClock : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->descriptionColor($oldestPending === 'baru saja' ? 'success' : 'warning')
                ->color($pendingCount > 0 ? 'info' : 'success')
                ->icon(Heroicon::OutlinedQueueList),

            Stat::make('Job Gagal', $failedCount)
                ->description($failedCount > 0 ? 'Ada yang perlu ditindaklanjuti' : 'Tidak ada kegagalan')
                ->descriptionIcon($failedCount > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($failedCount > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedShieldExclamation),

            Stat::make('Batch Aktif', $activeBatchCount)
                ->description($activeBatchCount > 0 ? 'Masih ada batch berjalan' : 'Tidak ada batch berjalan')
                ->descriptionIcon($activeBatchCount > 0 ? Heroicon::OutlinedArrowPath : Heroicon::OutlinedPauseCircle, IconPosition::Before)
                ->color($activeBatchCount > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedClipboardDocumentList),
        ];
    }

    protected function resolveOldestPendingAge(): string
    {
        $oldestCreatedAt = DB::table('jobs')->min('created_at');

        if ($oldestCreatedAt === null) {
            return 'baru saja';
        }

        $minutes = (int) floor((now()->timestamp - (int) $oldestCreatedAt) / 60);

        if ($minutes < 1) {
            return 'baru saja';
        }

        if ($minutes < 60) {
            return "{$minutes} menit lalu";
        }

        $hours = (int) floor($minutes / 60);

        if ($hours < 24) {
            return "{$hours} jam lalu";
        }

        return ((int) floor($hours / 24)).' hari lalu';
    }
}
