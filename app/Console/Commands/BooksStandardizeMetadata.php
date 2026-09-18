<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BooksStandardizeMetadata extends Command
{
    protected $signature = 'books:standardize-metadata';

    protected $description = 'Menormalkan metadata buku, penulis, dan penerbit (kapitalisasi, squish, format ISSN).';

    public function handle(): int
    {
        $counts = [];

        DB::transaction(function () use (&$counts): void {
            $counts['book'] = $this->standardizeBooks();
            $counts['placeholder'] = $this->clearPlaceholders();
            $counts['author'] = $this->standardizeAuthors();
            $counts['publisher'] = $this->standardizePublishers();
        });

        $this->info(sprintf(
            'Buku: %d dinormalisasi (termasuk %d placeholder "-" dikosongkan), Penulis: %d, Penerbit: %d.',
            $counts['book'],
            $counts['placeholder'],
            $counts['author'],
            $counts['publisher'],
        ));

        return self::SUCCESS;
    }

    protected function standardizeBooks(): int
    {
        $changed = 0;

        Book::query()->chunkById(100, function ($books) use (&$changed): void {
            foreach ($books as $book) {
                $before = $book->getAttributes();
                $book->title = $book->getRawOriginal('title');
                $book->subtitle = $book->getRawOriginal('subtitle');
                $book->description = $book->getRawOriginal('description');
                $book->edition = $book->getRawOriginal('edition');
                $book->pages = $book->getRawOriginal('pages');
                $book->ddc_code = $book->getRawOriginal('ddc_code');
                $book->language = $book->getRawOriginal('language');
                $book->issn = $book->getRawOriginal('issn');

                foreach (self::PLACEHOLDER_FIELDS as $field) {
                    if (self::isPlaceholder($book->{$field})) {
                        $book->{$field} = null;
                    }
                }

                if (self::isPlaceholder($book->getRawOriginal('language'))) {
                    $book->language = self::DEFAULT_LANGUAGE;
                }

                if ($book->isDirty()) {
                    $book->save();
                    $changed++;
                }
            }
        });

        return $changed;
    }

    protected function standardizeAuthors(): int
    {
        $changed = 0;

        Author::query()->chunkById(100, function ($authors) use (&$changed): void {
            foreach ($authors as $author) {
                $author->name = $author->getRawOriginal('name');

                if ($author->isDirty()) {
                    $author->save();
                    $changed++;
                }
            }
        });

        return $changed;
    }

    /**
     * Nullable columns that spreadsheets commonly export with a "-" or "N/A"
     * stand-in for an empty cell. Such placeholders must be stored as NULL.
     *
     * `language` is deliberately absent: its column is NOT NULL with a default
     * of "Indonesia", so a placeholder resets it to the default instead.
     *
     * @var array<int, string>
     */
    protected const PLACEHOLDER_FIELDS = [
        'subtitle',
        'description',
        'edition',
        'pages',
        'ddc_code',
        'issn',
        'isbn',
    ];

    /**
     * Default applied when `language` held a placeholder. Mirrors the column
     * default in the books migration.
     */
    protected const DEFAULT_LANGUAGE = 'Indonesia';

    /**
     * @var array<int, string>
     */
    protected const BLANK_PLACEHOLDERS = [
        '-', '--', '---',
        '–', '—', '−',
        'n/a', 'n.a.', 'na', 'null', 'nil', 'none',
        'tidak ada', 'tidak tersedia',
    ];

    /**
     * Blank out any placeholder glyph stored by an earlier import so the UI
     * stops rendering a stray "-" where a value was never provided.
     */
    protected function clearPlaceholders(): int
    {
        $cleared = 0;

        Book::query()->chunkById(100, function ($books) use (&$cleared): void {
            foreach ($books as $book) {
                $dirty = false;

                foreach (self::PLACEHOLDER_FIELDS as $field) {
                    if (self::isPlaceholder($book->getRawOriginal($field))) {
                        $book->{$field} = null;
                        $dirty = true;
                    }
                }

                if (self::isPlaceholder($book->getRawOriginal('language'))) {
                    $book->language = self::DEFAULT_LANGUAGE;
                    $dirty = true;
                }

                if ($dirty) {
                    $book->save();
                    $cleared++;
                }
            }
        });

        return $cleared;
    }

    protected static function isPlaceholder(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return in_array(mb_strtolower(trim($value)), self::BLANK_PLACEHOLDERS, true);
    }

    protected function standardizePublishers(): int
    {
        $changed = 0;

        Publisher::query()->chunkById(100, function ($publishers) use (&$changed): void {
            foreach ($publishers as $publisher) {
                $publisher->name = $publisher->getRawOriginal('name');
                $publisher->city = $publisher->getRawOriginal('city');

                if ($publisher->isDirty()) {
                    $publisher->save();
                    $changed++;
                }
            }
        });

        return $changed;
    }
}
