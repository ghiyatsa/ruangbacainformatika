<?php

namespace App\Filament\Resources\Loans\RelationManagers;

use App\Filament\Resources\Loans\Pages\ViewLoan;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static bool $isLazy = false;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewLoan::class
            && (auth()->user()?->hasAdministrativeRole() ?? false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('bookItem.book.title')
            ->columns([
                TextColumn::make('bookItem.book.title')
                    ->label('Judul Buku')
                    ->searchable(),
                TextColumn::make('bookItem.book.isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('bookItem.internal_code')
                    ->label('Kode Eksemplar')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('bookItem.status')
                    ->label('Status Item')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'borrowed' => 'Dipinjam',
                        'maintenance' => 'Perawatan',
                        'reserved' => 'Reservasi',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'borrowed' => 'warning',
                        'maintenance' => 'danger',
                        'reserved' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('returned_at')
                    ->label('Sudah Kembali')
                    ->boolean()
                    ->getStateUsing(fn (Model $record): bool => $record->returned_at !== null),
                TextColumn::make('returned_at')
                    ->label('Waktu Kembali')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
