<?php

declare(strict_types=1);

namespace App\Actions\Kiosk;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Hasil pencarian buku di kiosk.
 */
final class KioskBookSearchResult
{
    /**
     * @param  EloquentCollection<int, Book>  $books
     */
    public function __construct(
        public readonly EloquentCollection $books,
    ) {}
}
