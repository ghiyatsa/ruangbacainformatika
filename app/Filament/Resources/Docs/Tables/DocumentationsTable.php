<?php

namespace App\Filament\Resources\Docs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DocumentationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul panduan')
            ->emptyStateHeading('Belum ada panduan')
            ->emptyStateDescription('Tambahkan panduan operasional untuk admin dan staff.')
            ->columns([
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Judul Panduan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),

                TextColumn::make('summary')
                    ->label('Ringkasan')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),

                IconColumn::make('is_published')
                    ->label('Terbit')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updatedBy.name')
                    ->label('Diperbarui Oleh')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('documentation_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_published')
                    ->label('Status Terbit')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah terbit')
                    ->falseLabel('Masih draf'),
            ])
            ->recordActions([
                EditAction::make()->label('Ubah'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus terpilih'),
                ]),
            ]);
    }
}
