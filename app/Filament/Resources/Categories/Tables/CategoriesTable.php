<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama kategori')
            ->emptyStateHeading('Belum ada kategori')
            ->emptyStateDescription('Data kategori akan tampil di sini.')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),

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
                        ->before(function (DeleteAction $action, Category $record): void {
                            if (! $reason = $record->deletionBlockedReason()) {
                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Kategori tidak dapat dihapus')
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
                            /** @var Category|null $blockedRecord */
                            $blockedRecord = $records->first(fn (Category $record): bool => $record->deletionBlockedReason() !== null);

                            if (! $blockedRecord) {
                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Beberapa kategori tidak dapat dihapus')
                                ->body($blockedRecord->deletionBlockedReason() ?? 'Masih ada kategori yang digunakan oleh buku.')
                                ->send();

                            $action->halt();
                        }),
                ]),
            ]);
    }
}
