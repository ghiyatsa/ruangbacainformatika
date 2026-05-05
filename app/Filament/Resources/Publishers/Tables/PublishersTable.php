<?php

namespace App\Filament\Resources\Publishers\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublishersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari nama penerbit atau kota')
            ->emptyStateHeading('Belum ada penerbit')
            ->emptyStateDescription('Penerbit membantu menjaga metadata buku tetap rapi dan konsisten.')
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
                        ->label('Ubah Penerbit'),
                ])
                    ->label('Aksi'),
            ])
            ->toolbarActions([]);
    }
}
