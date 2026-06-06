<?php

namespace App\Filament\Resources\Publishers\Tables;

use App\Models\Publisher;
use App\Support\AppTimezone;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PublishersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama penerbit atau kota')
            ->emptyStateHeading('Belum ada penerbit')
            ->emptyStateDescription('Daftar penerbit akan muncul di sini.')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Penerbit')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('city')
                    ->label('Kota')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('books_count')
                    ->label('Jumlah Buku')
                    ->counts('books')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => ((int) $state) > 0 ? 'warning' : 'success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('has_books')
                    ->label('Buku Terbit')
                    ->placeholder('Semua Penerbit')
                    ->trueLabel('Memiliki Buku')
                    ->falseLabel('Tidak Memiliki Buku')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('books'),
                        false: fn (Builder $query) => $query->whereDoesntHave('books'),
                    ),
                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
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
                    DeleteAction::make()
                        ->label('Hapus')
                        ->before(function (DeleteAction $action, Publisher $record): void {
                            if (! $reason = $record->deletionBlockedReason()) {
                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Penerbit belum bisa dihapus')
                                ->body($reason)
                                ->send();

                            $action->halt();
                        }),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            /** @var Publisher|null $blockedRecord */
                            $blockedRecord = $records->first(fn (Publisher $record): bool => $record->deletionBlockedReason() !== null);

                            if (! $blockedRecord) {
                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Sebagian penerbit belum bisa dihapus')
                                ->body($blockedRecord->deletionBlockedReason() ?? 'Masih ada buku yang memakai penerbit ini.')
                                ->send();

                            $action->halt();
                        }),
                ]),
            ]);
    }
}
