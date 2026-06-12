<?php

use App\Filament\Exports\BookExporter;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\Publisher;

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
        ->toContain('items_count');
});
