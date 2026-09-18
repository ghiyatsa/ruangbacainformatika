<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Concerns\BookRelationManager;
use Filament\Tables\Columns\TextColumn;

class BooksRelationManager extends BookRelationManager
{
    protected static ?string $title = 'Daftar Buku dalam Kategori';

    protected function emptyStateHeading(): string
    {
        return 'Belum ada buku dalam kategori ini';
    }

    protected function emptyStateDescription(): string
    {
        return 'Kategori ini belum terhubung dengan buku apa pun.';
    }

    protected function contextColumn(): TextColumn
    {
        return TextColumn::make('publisher.name')
            ->label('Penerbit')
            ->searchable()
            ->sortable();
    }
}
