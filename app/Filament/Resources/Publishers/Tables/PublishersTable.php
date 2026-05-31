<?php

namespace App\Filament\Resources\Publishers\Tables;

use App\Models\Publisher;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PublishersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama penerbit atau kota')
            ->emptyStateHeading('Belum ada penerbit')
            ->emptyStateDescription('Data penerbit akan tampil di sini.')
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
                //
            ])
            ->recordActions([
                ActionGroup::make([
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
                                ->title('Penerbit tidak dapat dihapus')
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
                                ->title('Beberapa penerbit tidak dapat dihapus')
                                ->body($blockedRecord->deletionBlockedReason() ?? 'Masih ada penerbit yang digunakan oleh buku.')
                                ->send();

                            $action->halt();
                        }),
                ]),
            ]);
    }
}
