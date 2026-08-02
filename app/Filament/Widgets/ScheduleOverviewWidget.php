<?php

namespace App\Filament\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class ScheduleOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $tasks = MonitoredScheduledTask::query()
            ->get(['id', 'last_started_at', 'last_finished_at', 'last_failed_at']);

        $healthy = $failed = $neverRun = $inProgress = 0;

        foreach ($tasks as $task) {
            if ($task->last_started_at === null) {
                $neverRun++;

                continue;
            }

            if ($task->last_failed_at !== null
                && ($task->last_finished_at === null || $task->last_failed_at->greaterThan($task->last_finished_at))) {
                $failed++;

                continue;
            }

            if ($task->last_finished_at !== null) {
                $healthy++;

                continue;
            }

            $inProgress++;
        }

        $total = $tasks->count();
        $lastRunAt = MonitoredScheduledTaskLogItem::query()
            ->whereIn('type', [
                MonitoredScheduledTaskLogItem::TYPE_FINISHED,
                MonitoredScheduledTaskLogItem::TYPE_FAILED,
            ])
            ->latest('created_at')
            ->first()
            ?->created_at;

        return [
            Stat::make('Task Terjadwal', $total)
                ->description($total > 0 ? "{$healthy} sehat, {$failed} gagal" : 'Belum ada task')
                ->descriptionIcon($failed > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($failed > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedCalendarDays),

            Stat::make('Sehat', $healthy)
                ->description('Run terakhir selesai normal')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color('success')
                ->icon(Heroicon::OutlinedCheckBadge),

            Stat::make('Perlu Perhatian', $failed + $inProgress)
                ->description($failed > 0 ? 'Ada run gagal atau belum selesai' : 'Semua aman')
                ->descriptionIcon($failed + $inProgress > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($failed + $inProgress > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedShieldExclamation),

            Stat::make('Belum Pernah Jalan', $neverRun)
                ->description($neverRun > 0 ? 'Menunggu jadwal pertama' : 'Semua sudah terekam')
                ->descriptionIcon($neverRun > 0 ? Heroicon::OutlinedClock : Heroicon::OutlinedCheckCircle, IconPosition::Before)
                ->color($neverRun > 0 ? 'info' : 'gray')
                ->icon(Heroicon::OutlinedClock),

            Stat::make('Run Terakhir', $lastRunAt?->diffForHumans() ?? 'Belum ada')
                ->description('Selesai atau gagal terakhir')
                ->descriptionIcon(Heroicon::OutlinedArrowPath, IconPosition::Before)
                ->color('gray')
                ->icon(Heroicon::OutlinedCircleStack),
        ];
    }
}
