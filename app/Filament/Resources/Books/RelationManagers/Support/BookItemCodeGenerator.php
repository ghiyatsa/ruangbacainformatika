<?php

namespace App\Filament\Resources\Books\RelationManagers\Support;

use App\Models\Book;

class BookItemCodeGenerator
{
    public static function generate(Book $book, int $sequence, int $padding = 3): string
    {
        $prefix = 'INF-'.($book->isbn ?: str_pad((string) $book->id, 6, '0', STR_PAD_LEFT));
        $suffix = str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT);

        return "{$prefix}-{$suffix}";
    }
}
