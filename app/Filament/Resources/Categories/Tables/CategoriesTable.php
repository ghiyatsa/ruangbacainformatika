<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Support\Library\LibraryResourceActionFactory;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama kategori')
            ->emptyStateHeading('Belum ada kategori')
            ->emptyStateDescription('Kategori membantu pengelola mengelompokkan koleksi dengan cepat.')
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
                        ->label('Ubah Kategori'),
                    LibraryResourceActionFactory::deleteAction(
                        singularLabel: 'Kategori',
                        fallbackReason: 'Masih ada data terkait yang membuat kategori ini tidak bisa dihapus saat ini.',
                        modalDescription: 'Kategori hanya bisa dihapus jika sudah tidak dipakai oleh data buku mana pun.',
                    ),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    LibraryResourceActionFactory::deleteBulkAction(
                        singularLabel: 'kategori',
                        pluralLabel: 'kategori',
                        genericFailureReason: 'masih dipakai oleh data buku',
                    ),
                ]),
            ]);
    }
}
