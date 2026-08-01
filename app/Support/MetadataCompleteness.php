<?php

namespace App\Support;

use App\Models\Book;

class MetadataCompleteness
{
    public const LEVEL_LENGKAP = 'lengkap';

    public const LEVEL_SEBAGIAN = 'sebagian';

    public const LEVEL_KURANG = 'kurang';

    /**
     * Elemen metadata standar yang dinilai untuk setiap buku.
     *
     * @var array<string, string>
     */
    public const ELEMENTS = [
        'title' => 'Judul',
        'identifier' => 'ISBN/ISSN',
        'ddc_code' => 'Kode DDC',
        'description' => 'Deskripsi',
        'cover_image' => 'Sampul',
        'language' => 'Bahasa',
        'published_year' => 'Tahun terbit',
        'publisher_id' => 'Penerbit',
        'authors' => 'Penulis',
        'categories' => 'Kategori',
    ];

    public static function total(): int
    {
        return count(self::ELEMENTS);
    }

    /**
     * Menghitung kelengkapan metadata sebuah buku.
     *
     * @return array{score: int, filled: int, total: int, missing: array<int, string>, level: string}
     */
    public static function evaluate(Book $book): array
    {
        $missing = [];

        foreach (self::ELEMENTS as $key => $label) {
            if (! self::isFilled($book, $key)) {
                $missing[] = $label;
            }
        }

        $total = self::total();
        $filled = $total - count($missing);
        $score = (int) round($filled / $total * 100);

        return [
            'score' => $score,
            'filled' => $filled,
            'total' => $total,
            'missing' => array_values($missing),
            'level' => self::levelForScore($score),
        ];
    }

    public static function levelForScore(int $score): string
    {
        return match (true) {
            $score >= 100 => self::LEVEL_LENGKAP,
            $score >= 50 => self::LEVEL_SEBAGIAN,
            default => self::LEVEL_KURANG,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function levelOptions(): array
    {
        return [
            self::LEVEL_LENGKAP => 'Lengkap',
            self::LEVEL_SEBAGIAN => 'Perlu Kurasi',
            self::LEVEL_KURANG => 'Tidak Lengkap',
        ];
    }

    protected static function isFilled(Book $book, string $key): bool
    {
        return match ($key) {
            'title' => filled($book->title),
            'identifier' => filled($book->isbn) || filled($book->issn),
            'ddc_code' => filled($book->ddc_code),
            'description' => filled($book->description),
            'cover_image' => filled($book->cover_image),
            'language' => filled($book->language),
            'published_year' => filled($book->published_year),
            'publisher_id' => filled($book->publisher_id),
            'authors' => $book->relationLoaded('authors')
                ? $book->authors->isNotEmpty()
                : $book->authors()->exists(),
            'categories' => $book->relationLoaded('categories')
                ? $book->categories->isNotEmpty()
                : $book->categories()->exists(),
            default => false,
        };
    }

    /**
     * Ekspresi SQL jumlah elemen metadata yang terisi untuk query agregat/filter.
     */
    public static function filledSql(): string
    {
        return implode(' + ', [
            "CASE WHEN NULLIF(title, '') IS NOT NULL THEN 1 ELSE 0 END",
            "CASE WHEN (NULLIF(isbn, '') IS NOT NULL OR NULLIF(issn, '') IS NOT NULL) THEN 1 ELSE 0 END",
            "CASE WHEN NULLIF(ddc_code, '') IS NOT NULL THEN 1 ELSE 0 END",
            "CASE WHEN NULLIF(description, '') IS NOT NULL THEN 1 ELSE 0 END",
            "CASE WHEN NULLIF(cover_image, '') IS NOT NULL THEN 1 ELSE 0 END",
            "CASE WHEN NULLIF(language, '') IS NOT NULL THEN 1 ELSE 0 END",
            'CASE WHEN published_year IS NOT NULL THEN 1 ELSE 0 END',
            'CASE WHEN publisher_id IS NOT NULL THEN 1 ELSE 0 END',
            'CASE WHEN EXISTS (SELECT 1 FROM author_book WHERE author_book.book_id = books.id) THEN 1 ELSE 0 END',
            'CASE WHEN EXISTS (SELECT 1 FROM book_category WHERE book_category.book_id = books.id) THEN 1 ELSE 0 END',
        ]);
    }
}
