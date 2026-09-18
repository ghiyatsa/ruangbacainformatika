<?php

declare(strict_types=1);

namespace App\Filament\Resources\Publishers\RelationManagers;

use App\Filament\Resources\Concerns\BookRelationManager;
use Filament\Tables\Columns\TextColumn;

class BooksRelationManager extends BookRelationManager
{
    protected static ?string $title = 'Daftar Buku yang Diterbitkan';

    protected function emptyStateHeading(): string
    {
        return 'Belum ada buku yang diterbitkan';
    }

    protected function emptyStateDescription(): string
    {
        return 'Penerbit ini belum terhubung dengan buku apa pun.';
    }

    protected function contextColumn(): TextColumn
    {
        return TextColumn::make('authors.name')
            ->label('Penulis')
            ->badge()
            ->color('warning')
            ->placeholder('Tidak ada penulis');
    }

    protected function allowsAttachingBooks(): bool
    {
        return false;
    }
}
