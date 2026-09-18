<?php

declare(strict_types=1);

namespace App\Actions\Kiosk;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Hasil pencarian buku di kiosk: daftar buku beserta saran pencarian.
 */
final class KioskBookSearchResult
{
    /**
     * @param  EloquentCollection<int, Book>  $books
     * @param  list<string>  $suggestions
     */
    public function __construct(
        public readonly EloquentCollection $books,
        public readonly array $suggestions = [],
        public readonly ?string $correctedQuery = null,
    ) {}
}
