<?php

namespace App\Filament\Resources\Skripsis\Tables;

use App\Filament\Imports\SkripsiImporter;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Services\SimilaritySyncDispatcher;
use App\Services\SimilaritySyncStatusService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class SkripsisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul, nama, atau NIM')
            ->emptyStateHeading('Belum ada data skripsi')
            ->emptyStateDescription('Data skripsi akan tampil di sini.')
            ->emptyStateIcon(Heroicon::OutlinedNewspaper)
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Skripsi $record): ?string => filled($record->keywords)
                        ? 'Kata kunci: '.Str::limit($record->keywords, 80)
                        : null)
                    ->wrap(),
                TextColumn::make('author_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Skripsi $record): string => "NIM: {$record->student_id}"),
                TextColumn::make('student_id')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('similarity_sync_status')
                    ->label('Status Similarity')
                    ->state(fn (Skripsi $record): string => $record->similaritySyncStatusLabel())
                    ->badge()
                    ->color(fn (Skripsi $record): string => $record->similaritySyncStatusColor()),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('abstract')
                    ->label('Abstrak')
                    ->limit(80)
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
                    ->label('Status sync')
                    ->options(SimilaritySyncStatus::statusOptions())
                    ->query(fn ($query, array $data) => $query->when(
                        filled($data['value'] ?? null),
                        fn ($query) => $query->whereHas(
                            'similaritySyncStatus',
                            fn ($query) => $query->where('status', $data['value']),
                        ),
                    )),
                Filter::make('perlu_sync')
                    ->label('Perlu diproses')
                    ->query(fn ($query) => $query->whereHas(
                        'similaritySyncStatus',
                        fn ($query) => $query->whereIn('status', [
                            SimilaritySyncStatus::STATUS_PENDING,
                            SimilaritySyncStatus::STATUS_SYNCING,
                        ]),
                    )),
                Filter::make('belum_dijadwalkan')
                    ->label('Belum dijadwalkan')
                    ->query(fn ($query) => $query->whereDoesntHave('similaritySyncStatus')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon(Heroicon::OutlinedEye)
                    ->hiddenLabel()
                    ->tooltip('Lihat'),
                EditAction::make()
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->hiddenLabel()
                    ->tooltip('Ubah'),
                Action::make('retrySync')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->hiddenLabel()
                    ->tooltip('Sinkronkan')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Skripsi $record): void {
                        app(SimilaritySyncStatusService::class)->markQueued($record);
                        app(SimilaritySyncDispatcher::class)->dispatchUpsert($record->getKey());

                        Notification::make()
                            ->success()
                            ->title('Sinkron dijadwalkan')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                ImportAction::make('importSkripsi')
                    ->importer(SkripsiImporter::class)
                    ->chunkSize(25)
                    ->label('Impor')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('info'),
                BulkActionGroup::make([
                    BulkAction::make('retrySelectedSync')
                        ->label('Sinkronkan Terpilih')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('info')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->chunkSelectedRecords(100)
                        ->action(function (LazyCollection $records): void {
                            $queuedCount = 0;

                            foreach ($records as $record) {
                                app(SimilaritySyncStatusService::class)->markQueued($record);
                                app(SimilaritySyncDispatcher::class)->dispatchUpsert($record->getKey());
                                $queuedCount++;
                            }

                            Notification::make()
                                ->success()
                                ->title($queuedCount.' skripsi dijadwalkan')
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
        return Skripsi::query()
            ->whereNotNull('year')
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year', 'year')
            ->all();
    }
}
