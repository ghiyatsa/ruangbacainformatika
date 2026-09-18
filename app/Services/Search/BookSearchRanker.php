<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ranking relevansi buku, dipakai bersama pencarian web dan kiosk.
 *
 * Prioritas: judul persis > awalan judul > judul memuat kata kunci >
 * subjudul > ISBN/ISSN/DDC > deskripsi. Setiap kata kunci yang muncul di
 * judul menambah skor sehingga karya yang judulnya memuat lebih banyak kata
 * kunci terangkat ke atas.
 */
class BookSearchRanker
{
    /**
     * @param  Builder<Book>  $query
     */
    public function apply(Builder $query, string $search): void
    {
        $exact = $search;
        $prefix = "{$search}%";
        $wildcard = "%{$search}%";

        $kata = preg_split('/[^\p{L}\p{N}]+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $kata = array_values(array_unique(array_map(
            fn (string $k): string => mb_strtolower($k),
            $kata,
        )));

        $skorJudul = '';
        $binding = [];
        foreach ($kata as $k) {
            $skorJudul .= ' + (CASE WHEN LOWER(books.title) LIKE ? THEN 25 ELSE 0 END)';
            $skorJudul .= ' + (CASE WHEN LOWER(books.subtitle) LIKE ? THEN 8 ELSE 0 END)';
            $binding[] = '%'.$k.'%';
            $binding[] = '%'.$k.'%';
        }

        // Bonus kedekatan: frasa utuh di judul menandakan kecocokan terkuat.
        $bonus = $kata !== [] && count($kata) > 1 ? 60 : 0;

        $query
            ->selectRaw(
                'CASE
                    WHEN books.title = ? THEN 100
                    WHEN books.title LIKE ? THEN 80
                    WHEN books.title LIKE ? THEN 60
                    WHEN books.subtitle LIKE ? THEN 40
                    WHEN books.isbn LIKE ? OR books.issn LIKE ? OR books.ddc_code LIKE ? THEN 30
                    WHEN books.description LIKE ? THEN 10
                    ELSE 5
                END
                + '.($bonus > 0 ? '60' : '0').$skorJudul.'
                as search_priority',
                [
                    $exact,
                    $prefix,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    $wildcard,
                    ...$binding,
                ]
            )
            ->orderByDesc('search_priority')
            ->orderByDesc('books.view_count')
            ->orderBy('books.title');
    }
}
