<?php

namespace App\Filament\Resources\Authors\RelationManagers;

use App\Filament\Resources\Concerns\BookRelationManager;
use Filament\Tables\Columns\TextColumn;

class BooksRelationManager extends BookRelationManager
{
    protected static ?string $title = 'Daftar Buku yang Ditulis';

    protected function emptyStateHeading(): string
    {
        return 'Belum ada buku yang ditulis';
    }

    protected function emptyStateDescription(): string
    {
        return 'Penulis ini belum terhubung dengan buku apa pun.';
    }

    protected function contextColumn(): TextColumn
    {
        return TextColumn::make('publisher.name')
            ->label('Penerbit')
            ->searchable()
            ->sortable();
    }
}
