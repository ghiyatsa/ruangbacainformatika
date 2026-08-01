<?php

use App\Filament\Exports\BookExporter;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\Publisher;
use Filament\Actions\Exports\Models\Export;

it('defines the correct columns for book export', function () {
    $publisher = Publisher::factory()->create(['name' => 'Gramedia']);
    $author = Author::factory()->create(['name' => 'Andrea Hirata']);
    $category = Category::factory()->create(['name' => 'Fiksi']);

    /** @var Book $book */
    $book = Book::factory()->create([
        'title' => 'Laskar Pelangi',
        'subtitle' => 'Mimpi & Harapan',
        'isbn' => '9786022916628',
        'pages' => 'xiv + 529',
        'publisher_id' => $publisher->id,
    ]);

    $book->authors()->attach($author);
    $book->categories()->attach($category);

    BookItem::factory()->count(3)->create(['book_id' => $book->id]);

    $columns = BookExporter::getColumns();

    expect($columns)->not->toBeEmpty();

    $columnNames = collect($columns)->map(fn ($column) => $column->getName())->all();

    expect($columnNames)->toContain('id')
        ->toContain('title')
        ->toContain('subtitle')
        ->toContain('isbn')
        ->toContain('pages')
        ->toContain('publisher.name')
        ->toContain('authors')
        ->toContain('categories')
        ->toContain('items_count')
        ->toContain('cover_image')
        ->toContain('shelf_locations');
});

it('exports cover url and unique shelf locations for a book', function () {
    $publisher = Publisher::factory()->create(['name' => 'Gramedia']);
    $book = Book::factory()->create([
        'title' => 'Laskar Pelangi',
        'cover_image' => 'books/covers/laskar.webp',
        'publisher_id' => $publisher->id,
    ]);

    foreach (['R-01-A', 'R-01-A', 'ARSIP-02', null] as $index => $location) {
        BookItem::factory()->create([
            'book_id' => $book->id,
            'internal_code' => "LP-{$index}",
            'shelf_location' => $location,
        ]);
    }

    $book->load(['authors', 'categories', 'items']);
    $export = new Export;
    $export->exporter = BookExporter::class;

    $columnMap = collect(BookExporter::getColumns())
        ->mapWithKeys(fn ($column) => [$column->getName() => $column->getLabel()])
        ->all();

    $exporter = app(BookExporter::class, [
        'export' => $export,
        'columnMap' => $columnMap,
        'options' => [],
    ]);

    $result = collect(array_combine(array_keys($columnMap), $exporter($book)));

    expect($result['cover_image'])->toBe(asset('storage/books/covers/laskar.webp'))
        ->and($result['shelf_locations'])->toBe('R-01-A | ARSIP-02')
        ->and($result['items_count'])->toBe('4');
});
