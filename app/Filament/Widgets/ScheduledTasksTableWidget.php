<?php

namespace App\Filament\Widgets;

use Cron\CronExpression;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Spatie\ScheduleMonitor\Support\ScheduledTasks\ScheduledTaskFactory;

class ScheduledTasksTableWidget extends BaseTableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(MonitoredScheduledTask::query()->orderBy('name'))
            ->columns([
                TextColumn::make('name')
                    ->label('Task')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->iconPosition('before')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('cron_expression')
                    ->label('Jadwal')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (MonitoredScheduledTask $record): string => $this->resolveLastRunStatus($record))
                    ->badge()
                    ->color(fn (MonitoredScheduledTask $record): string => $this->resolveLastRunStatusColor($record)),

                TextColumn::make('last_finished_at')
                    ->label('Run Terakhir')
                    ->since()
                    ->placeholder('Belum pernah'),

                TextColumn::make('next_run')
                    ->label('Run Berikutnya')
                    ->state(fn (MonitoredScheduledTask $record): ?Carbon => $this->resolveNextRunAt($record))
                    ->since()
                    ->placeholder('Tidak dapat dihitung'),
            ])
            ->recordActions([
                Action::make('runNow')
                    ->label('Jalankan Sekarang')
                    ->icon(Heroicon::OutlinedPlay)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Jalankan Task Sekarang')
                    ->modalDescription(fn (MonitoredScheduledTask $record): string => "Task {$record->name} akan dieksekusi langsung dari definisi jadwalnya.")
                    ->action(function (MonitoredScheduledTask $record): void {
                        $this->runTaskNow($record);
                    }),
            ])
            ->headerActions([
                Action::make('syncSchedule')
                    ->label('Sinkronkan Jadwal')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronkan Jadwal')
                    ->modalDescription('Mendaftarkan task terjadwal terbaru dari routes/console.php ke pemantauan.')
                    ->action(function (): void {
                        Artisan::call('schedule-monitor:sync');

                        Notification::make()
                            ->title('Jadwal disinkronkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCalendarDays)
            ->emptyStateHeading('Belum ada task terjadwal')
            ->emptyStateDescription('Jalankan aksi Sinkronkan Jadwal untuk mendaftarkan task dari routes/console.php.')
            ->paginated([10, 25, 50]);
    }

    protected function runTaskNow(MonitoredScheduledTask $record): void
    {
        $this->ensureConsoleScheduleLoaded();

        $event = collect(app(Schedule::class)->events())
            ->first(function ($event) use ($record): bool {
                return ScheduledTaskFactory::createForEvent($event)->name() === $record->name;
            });

        if ($event === null) {
            Notification::make()
                ->title('Task tidak ditemukan di jadwal aktif')
                ->danger()
                ->send();

            return;
        }

        $event->run(app());

        if ($event->skippedBecauseOverlapping) {
            Notification::make()
                ->title("Task {$record->name} dilewati karena masih ada proses berjalan")
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title("Task {$record->name} telah dijalankan")
            ->success()
            ->send();
    }

    protected function ensureConsoleScheduleLoaded(): void
    {
        if (count(app(Schedule::class)->events()) > 0) {
            return;
        }

        require base_path('routes/console.php');
    }

    protected function resolveLastRunStatus(MonitoredScheduledTask $record): string
    {
        if ($record->last_failed_at !== null
            && ($record->last_finished_at === null || $record->last_failed_at->greaterThan($record->last_finished_at))) {
            return 'Gagal';
        }

        if ($record->last_finished_at !== null) {
            return 'Selesai';
        }

        if ($record->last_started_at !== null) {
            return 'Berjalan';
        }

        return 'Belum pernah';
    }

    protected function resolveLastRunStatusColor(MonitoredScheduledTask $record): string
    {
        if ($record->last_failed_at !== null
            && ($record->last_finished_at === null || $record->last_failed_at->greaterThan($record->last_finished_at))) {
            return 'danger';
        }

        if ($record->last_finished_at !== null) {
            return 'success';
        }

        if ($record->last_started_at !== null) {
            return 'warning';
        }

        return 'gray';
    }

    protected function resolveNextRunAt(MonitoredScheduledTask $record): ?Carbon
    {
        if (blank($record->cron_expression)) {
            return null;
        }

        $timezone = $record->timezone ?: config('app.timezone');

        $next = (new CronExpression($record->cron_expression))
            ->getNextRunDate(now(), 0, false, $timezone);

        return Carbon::instance($next);
    }
}
