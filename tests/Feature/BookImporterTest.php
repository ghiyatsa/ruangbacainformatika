<?php

use App\Filament\Imports\BookImporter;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Publisher;
use Filament\Actions\Imports\Models\Import;

function makeBookImporter(): BookImporter
{
    return new BookImporter(new Import, [
        'title' => 'title',
        'subtitle' => 'subtitle',
        'isbn' => 'isbn',
        'authors' => 'authors',
        'categories' => 'categories',
        'ddc_code' => 'ddc_code',
        'description' => 'description',
        'edition' => 'edition',
        'published_year' => 'published_year',
        'pages' => 'pages',
        'stock' => 'stock',
        'rack' => 'rack',
        'publisher' => 'publisher',
        'language' => 'language',
        'is_featured' => 'is_featured',
        'is_published' => 'is_published',
    ], []);
}

it('imports a book row with relations, stock, and rack location', function () {
    $importer = makeBookImporter();

    $importer([
        'title' => '  C++ Primer  ',
        'subtitle' => ' Panduan Dasar ',
        'isbn' => '978-602-1234-563',
        'authors' => ' Bjarne Stroustrup | Bjarne Stroustrup | Herb Sutter ',
        'categories' => ' Pemrograman | C++ | Pemrograman ',
        'ddc_code' => '005.13',
        'description' => ' Buku referensi pemrograman C++. ',
        'edition' => 'Edisi 5',
        'published_year' => '2024',
        'pages' => '1200',
        'stock' => '3',
        'rack' => ' R-02-B ',
        'publisher' => ' Addison-Wesley ',
        'language' => '',
        'is_featured' => '1',
        'is_published' => '1',
    ]);

    $book = Book::query()->where('isbn', '9786021234563')->first();

    expect($book)->not->toBeNull()
        ->and($book?->title)->toBe('C++ Primer')
        ->and($book?->slug)->toBe('c-plus-plus-primer')
        ->and($book?->subtitle)->toBe('Panduan Dasar')
        ->and($book?->language)->toBe('Indonesia')
        ->and($book?->publisher?->name)->toBe('Addison-Wesley')
        ->and($book?->authors()->pluck('name')->all())->toBe(['Bjarne Stroustrup', 'Herb Sutter'])
        ->and($book?->categories()->pluck('name')->all())->toBe(['Pemrograman', 'C++'])
        ->and($book?->items()->count())->toBe(3)
        ->and($book?->items()->pluck('shelf_location')->unique()->values()->all())->toBe(['R-02-B']);
});

it('updates existing books by isbn and only backfills missing shelf locations', function () {
    $publisher = Publisher::factory()->create(['name' => 'Legacy Press']);

    $book = Book::factory()->create([
        'title' => 'Legacy C++',
        'isbn' => '9786020000008',
        'publisher_id' => $publisher->getKey(),
    ]);

    BookItem::factory()->for($book)->create([
        'internal_code' => 'LEG-001',
        'shelf_location' => '-',
    ]);

    BookItem::factory()->for($book)->create([
        'internal_code' => 'LEG-002',
        'shelf_location' => 'ARSIP-A1',
    ]);

    $importer = makeBookImporter();

    $importer([
        'title' => 'Legacy C Plus Plus',
        'isbn' => '9786020000008',
        'authors' => ' Admin Legacy ',
        'categories' => ' Arsip ',
        'stock' => '3',
        'rack' => ' R-09-C ',
        'publisher' => 'Legacy Press',
        'language' => 'Indonesia',
        'is_featured' => '0',
        'is_published' => '1',
    ]);

    $book->refresh();

    expect($book->title)->toBe('Legacy C Plus Plus')
        ->and($book->slug)->toBe('legacy-c-plus-plus')
        ->and($book->items()->count())->toBe(3)
        ->and($book->items()->where('internal_code', 'LEG-001')->value('shelf_location'))->toBe('R-09-C')
        ->and($book->items()->where('internal_code', 'LEG-002')->value('shelf_location'))->toBe('ARSIP-A1')
        ->and($book->items()->where('internal_code', '!=', 'LEG-001')->where('internal_code', '!=', 'LEG-002')->value('shelf_location'))->toBe('R-09-C');
});

it('imports valid isbn-10 values with an uppercase x check digit', function () {
    $importer = makeBookImporter();

    $importer([
        'title' => 'The Elements of Style',
        'isbn' => '0-8044-2957-x',
        'publisher' => 'Pearson',
        'language' => 'Inggris',
        'is_featured' => '0',
        'is_published' => '1',
    ]);

    $book = Book::query()->where('isbn', '080442957X')->first();

    expect($book)->not->toBeNull()
        ->and($book?->isbn)->toBe('080442957X')
        ->and($book?->publisher?->name)->toBe('Pearson');
});

it('imports local 8 digit isbn values', function () {
    $importer = makeBookImporter();

    $importer([
        'title' => 'Buku Referensi Lokal',
        'isbn' => '1234-5678',
        'publisher' => 'Penerbit Lokal',
        'language' => 'Indonesia',
        'is_featured' => '0',
        'is_published' => '1',
    ]);

    $book = Book::query()->where('isbn', '12345678')->first();

    expect($book)->not->toBeNull()
        ->and($book?->isbn)->toBe('12345678')
        ->and($book?->publisher?->name)->toBe('Penerbit Lokal');
});
