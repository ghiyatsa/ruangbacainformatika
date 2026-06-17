<?php

use App\Filament\Imports\BookImporter;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Publisher;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\ValidationException;

function makeBookImporter(): BookImporter
{
    return new BookImporter(new Import, [
        'title' => 'title',
        'subtitle' => 'subtitle',
        'isbn' => 'isbn',
        'issn' => 'issn',
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

it('imports isbn values that match the expected format even when checksum validation would fail', function () {
    $importer = makeBookImporter();

    $importer([
        'title' => 'Buku Cetak Lama',
        'isbn' => '9786028599000',
        'publisher' => 'Penerbit Arsip',
        'language' => 'Indonesia',
        'is_featured' => '0',
        'is_published' => '1',
    ]);

    $book = Book::query()->where('isbn', '9786028599000')->first();

    expect($book)->not->toBeNull()
        ->and($book?->isbn)->toBe('9786028599000')
        ->and($book?->publisher?->name)->toBe('Penerbit Arsip');
});

it('imports a book row with issn and no isbn', function () {
    $importer = makeBookImporter();

    $importer([
        'title' => 'Jurnal Informatika',
        'subtitle' => 'Edisi Khusus',
        'isbn' => '',
        'issn' => '1234-5678',
        'authors' => 'Penulis Jurnal',
        'categories' => 'Jurnal',
        'ddc_code' => '005',
        'description' => 'Deskripsi Jurnal.',
        'edition' => 'Vol. 1',
        'published_year' => '2025',
        'pages' => '100',
        'stock' => '2',
        'rack' => 'R-01-A',
        'publisher' => 'IT Press',
        'language' => 'Indonesia',
        'is_featured' => '0',
        'is_published' => '1',
    ]);

    $book = Book::query()->where('issn', '1234-5678')->first();

    expect($book)->not->toBeNull()
        ->and($book?->title)->toBe('Jurnal Informatika')
        ->and($book?->issn)->toBe('1234-5678')
        ->and($book?->isbn)->toBeNull()
        ->and($book?->publisher?->name)->toBe('IT Press');
});

it('updates existing books by issn when isbn is blank', function () {
    $publisher = Publisher::factory()->create(['name' => 'IT Press']);
    $book = Book::factory()->create([
        'title' => 'Jurnal Informatika Lama',
        'issn' => '1234-5678',
        'isbn' => null,
        'edition' => 'Vol. 1',
        'pages' => '100',
        'publisher_id' => $publisher->getKey(),
    ]);

    $importer = makeBookImporter();

    $importer([
        'title' => 'Jurnal Informatika Baru',
        'isbn' => '',
        'issn' => '1234-5678',
        'authors' => 'Penulis Jurnal',
        'categories' => 'Jurnal',
        'ddc_code' => '005',
        'description' => 'Deskripsi Baru.',
        'edition' => 'Vol. 1',
        'published_year' => '2025',
        'pages' => '100',
        'stock' => '2',
        'rack' => 'R-01-A',
        'publisher' => 'IT Press',
        'language' => 'Indonesia',
        'is_featured' => '0',
        'is_published' => '1',
    ]);

    $book->refresh();

    expect($book->title)->toBe('Jurnal Informatika Baru')
        ->and($book->issn)->toBe('1234-5678')
        ->and($book->edition)->toBe('Vol. 1')
        ->and($book->pages)->toBe('100');
});

it('imports journal rows with the same issn when edition and pages differ', function () {
    $importer = makeBookImporter();

    $importer([
        'title' => 'Jurnal Informatika Vol. 1',
        'isbn' => '',
        'issn' => '1234-5678',
        'edition' => 'Vol. 1',
        'pages' => '100-120',
        'publisher' => 'IT Press',
        'language' => 'Indonesia',
        'is_featured' => '0',
        'is_published' => '1',
    ]);

    $importer([
        'title' => 'Jurnal Informatika Vol. 2',
        'isbn' => '',
        'issn' => '1234-5678',
        'edition' => 'Vol. 2',
        'pages' => '121-145',
        'publisher' => 'IT Press',
        'language' => 'Indonesia',
        'is_featured' => '0',
        'is_published' => '1',
    ]);

    expect(Book::query()->where('issn', '1234-5678')->count())->toBe(2)
        ->and(Book::query()->where('issn', '1234-5678')->pluck('edition')->all())->toBe(['Vol. 1', 'Vol. 2']);
});

it('requires edition and pages when importing an issn entry without isbn', function () {
    $importer = makeBookImporter();

    expect(fn () => $importer([
        'title' => 'Jurnal Tanpa Detail Terbit',
        'isbn' => '',
        'issn' => '1234-5678',
        'publisher' => 'IT Press',
        'language' => 'Indonesia',
        'is_featured' => '0',
        'is_published' => '1',
    ]))->toThrow(ValidationException::class);
});
