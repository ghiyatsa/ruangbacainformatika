<?php

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
            $counts['author'] = $this->standardizeAuthors();
            $counts['publisher'] = $this->standardizePublishers();
        });

        $this->info(sprintf(
            'Buku: %d dinormalisasi, Penulis: %d, Penerbit: %d.',
            $counts['book'],
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
