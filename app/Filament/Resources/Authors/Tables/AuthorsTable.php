<?php

namespace App\Filament\Resources\Authors\Tables;

use App\Support\Library\LibraryResourceActionFactory;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama penulis atau email')
            ->emptyStateHeading('Belum ada data penulis')
            ->emptyStateDescription('Tambahkan penulis agar katalog buku lebih mudah ditelusuri.')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
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
                        ->label('Ubah Penulis'),
                    LibraryResourceActionFactory::deleteAction(
                        singularLabel: 'Penulis',
                        fallbackReason: 'Masih ada data terkait yang membuat penulis ini tidak bisa dihapus saat ini.',
                        modalDescription: 'Penulis hanya bisa dihapus jika sudah tidak terhubung dengan data buku mana pun.',
                    ),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    LibraryResourceActionFactory::deleteBulkAction(
                        singularLabel: 'penulis',
                        pluralLabel: 'penulis',
                        genericFailureReason: 'masih terhubung dengan data buku',
                    ),
                ]),
            ]);
    }
}
