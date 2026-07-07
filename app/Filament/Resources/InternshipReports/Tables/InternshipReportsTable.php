<?php

namespace App\Filament\Resources\InternshipReports\Tables;

use App\Filament\Exports\InternshipReportExporter;
use App\Filament\Imports\InternshipReportImporter;
use App\Models\InternshipReport;
use App\Models\SimilaritySyncStatus;
use App\Services\SimilaritySyncDispatcher;
use App\Services\SimilaritySyncStatusService;
use App\Support\AppTimezone;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;

class InternshipReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul, nama, atau NIM')
            ->emptyStateHeading('Belum ada data laporan KP')
            ->emptyStateDescription('Data laporan KP akan muncul di sini.')
            ->emptyStateIcon(Heroicon::OutlinedNewspaper)
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->search($search))
                    ->sortable()
                    ->wrap(),
                TextColumn::make('author_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (InternshipReport $record): string => "NIM: {$record->student_id}"),
                TextColumn::make('student_id')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('similarity_sync_status')
                    ->label('Status Similarity')
                    ->state(fn (InternshipReport $record): string => $record->similaritySyncStatusLabel())
                    ->badge()
                    ->color(fn (InternshipReport $record): string => $record->similaritySyncStatusColor()),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('abstract')
                    ->label('Abstrak')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('keywords')
                    ->label('Kata Kunci')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('similaritySyncStatus.last_attempt_at')
                    ->label('Percobaan Terakhir')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('similaritySyncStatus.last_synced_at')
                    ->label('Terakhir Sinkron')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('similaritySyncStatus.last_error')
                    ->label('Error Terakhir')
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(fn (): array => static::yearOptions()),
                SelectFilter::make('similarity_status')
                    ->label('Status Similarity')
                    ->options(SimilaritySyncStatus::statusOptions())
                    ->query(function ($query, array $data) {
                        if (blank($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas(
                            'similaritySyncStatus',
                            fn ($q) => $q->where('status', $data['value'])
                        );
                    }),
                Filter::make('perlu_sync')
                    ->label('Perlu Sinkronisasi')
                    ->query(function ($query) {
                        return $query->where(function ($q) {
                            $q->whereDoesntHave('similaritySyncStatus')
                                ->orWhereHas(
                                    'similaritySyncStatus',
                                    fn ($sub) => $sub->whereIn('status', [
                                        SimilaritySyncStatus::STATUS_PENDING,
                                        SimilaritySyncStatus::STATUS_SYNCING,
                                        SimilaritySyncStatus::STATUS_FAILED,
                                    ])
                                );
                        });
                    }),
                Filter::make('belum_dijadwalkan')
                    ->label('Belum Dijadwalkan')
                    ->query(fn ($query) => $query->whereDoesntHave('similaritySyncStatus')),
                Filter::make('created_at')
                    ->label('Tanggal Masuk')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['from'],
                            function (Builder $query, $date): Builder {
                                [$startOfDay] = AppTimezone::dayRange($date);

                                return $query->where('created_at', '>=', $startOfDay);
                            }
                        )->when(
                            $data['until'],
                            function (Builder $query, $date): Builder {
                                [, $endOfDay] = AppTimezone::dayRange($date);

                                return $query->where('created_at', '<=', $endOfDay);
                            }
                        )
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),
                    EditAction::make()
                        ->label('Ubah'),
                    Action::make('retrySync')
                        ->label('Sinkronkan')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (InternshipReport $record): void {
                            app(SimilaritySyncStatusService::class)->markQueued($record);
                            app(SimilaritySyncDispatcher::class)->dispatchUpsert($record->getKey(), InternshipReport::class);

                            Notification::make()
                                ->success()
                                ->title('Sinkron masuk antrean')
                                ->send();
                        }),
                    DeleteAction::make()
                        ->label('Hapus'),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([
                ImportAction::make('importInternshipReport')
                    ->importer(InternshipReportImporter::class)
                    ->label('Impor')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('info'),
                ExportAction::make('exportInternshipReport')
                    ->exporter(InternshipReportExporter::class)
                    ->label('Ekspor')
                    ->icon(Heroicon::OutlinedDocumentArrowUp)
                    ->color('success'),
                BulkActionGroup::make([
                    BulkAction::make('retrySelectedSync')
                        ->label('Sinkronkan Terpilih')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('info')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->chunkSelectedRecords(100)
                        ->action(function (LazyCollection $records): void {
                            $ids = $records->map(fn (InternshipReport $record): int => $record->getKey())->all();
                            $queuedCount = count($ids);

                            app(SimilaritySyncStatusService::class)->markQueuedMultiple($ids, SimilaritySyncStatus::OPERATION_UPSERT, InternshipReport::class);
                            app(SimilaritySyncDispatcher::class)->dispatchBulkUpsert($ids, InternshipReport::class);

                            Notification::make()
                                ->success()
                                ->title($queuedCount.' laporan KP masuk antrean')
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }

    /**
     * @return array<int|string, int|string>
     */
    protected static function yearOptions(): array
    {
        return InternshipReport::query()
            ->whereNotNull('year')
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year', 'year')
            ->all();
    }
}
