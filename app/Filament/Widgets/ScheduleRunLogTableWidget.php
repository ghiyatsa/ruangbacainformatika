<?php

namespace App\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class ScheduleRunLogTableWidget extends BaseTableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MonitoredScheduledTaskLogItem::query()
                    ->with('monitoredScheduledTask')
                    ->whereIn('type', [
                        MonitoredScheduledTaskLogItem::TYPE_FINISHED,
                        MonitoredScheduledTaskLogItem::TYPE_FAILED,
                        MonitoredScheduledTaskLogItem::TYPE_SKIPPED,
                    ])
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('monitoredScheduledTask.name')
                    ->label('Task')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->iconPosition('before')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('type')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        MonitoredScheduledTaskLogItem::TYPE_FINISHED => 'Selesai',
                        MonitoredScheduledTaskLogItem::TYPE_FAILED => 'Gagal',
                        MonitoredScheduledTaskLogItem::TYPE_SKIPPED => 'Dilewati',
                        default => 'Mulai',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        MonitoredScheduledTaskLogItem::TYPE_FINISHED => 'success',
                        MonitoredScheduledTaskLogItem::TYPE_FAILED => 'danger',
                        MonitoredScheduledTaskLogItem::TYPE_SKIPPED => 'gray',
                        default => 'info',
                    })
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        MonitoredScheduledTaskLogItem::TYPE_FINISHED => Heroicon::OutlinedCheckCircle,
                        MonitoredScheduledTaskLogItem::TYPE_FAILED => Heroicon::OutlinedExclamationTriangle,
                        MonitoredScheduledTaskLogItem::TYPE_SKIPPED => Heroicon::OutlinedMinusCircle,
                        default => Heroicon::OutlinedArrowPath,
                    }),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since()
                    ->sortable(),

                TextColumn::make('meta.runtime')
                    ->label('Durasi')
                    ->state(fn (MonitoredScheduledTaskLogItem $record): string => $this->formatRuntime($record->meta['runtime'] ?? null)),

                TextColumn::make('meta.memory')
                    ->label('Memori')
                    ->state(fn (MonitoredScheduledTaskLogItem $record): string => $this->formatMemory($record->meta['memory'] ?? null)),

                TextColumn::make('meta.failure_message')
                    ->label('Keterangan')
                    ->state(fn (MonitoredScheduledTaskLogItem $record): ?string => $record->meta['failure_message'] ?? null)
                    ->limit(60)
                    ->color('danger')
                    ->tooltip(fn (MonitoredScheduledTaskLogItem $record): ?string => $record->meta['failure_message'] ?? null)
                    ->placeholder('Berjalan normal'),

                TextColumn::make('meta.output')
                    ->label('Output')
                    ->state(fn (MonitoredScheduledTaskLogItem $record): ?string => $record->meta['output'] ?? null)
                    ->limit(60)
                    ->tooltip(fn (MonitoredScheduledTaskLogItem $record): ?string => $record->meta['output'] ?? null)
                    ->placeholder('-'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedClock)
            ->emptyStateHeading('Belum ada riwayat run')
            ->emptyStateDescription('Riwayat akan tercatat setelah jadwal berjalan. Jalankan schedule-monitor:sync pada deploy.')
            ->paginated([15, 30, 50]);
    }

    protected function formatRuntime(mixed $runtime): string
    {
        if ($runtime === null) {
            return '-';
        }

        $seconds = (float) $runtime;

        if ($seconds < 60) {
            return number_format($seconds, 1, ',', '.').' dtk';
        }

        return number_format($seconds / 60, 1, ',', '.').' mnt';
    }

    protected function formatMemory(mixed $memory): string
    {
        if ($memory === null) {
            return '-';
        }

        return number_format((float) $memory / 1024 / 1024, 1, ',', '.').' MB';
    }
}
