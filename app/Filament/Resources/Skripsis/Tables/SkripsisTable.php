<?php

namespace App\Filament\Resources\Skripsis\Tables;

use App\Filament\Imports\SkripsiImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SkripsisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari judul, nama, atau NIM')
            ->emptyStateHeading('Belum ada data skripsi')
            ->emptyStateDescription('Tambahkan skripsi baru atau impor data agar katalog mulai terisi.')
            ->emptyStateIcon(Heroicon::OutlinedNewspaper)
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('author_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student_id')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
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
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                ImportAction::make('importSkripsi')
                    ->importer(SkripsiImporter::class)
                    ->label('Impor')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('info'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
