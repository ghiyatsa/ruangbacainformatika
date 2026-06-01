<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;

it('generates distinct slugs for categories such as C and C++', function () {
    $plainCategory = Category::query()->create([
        'name' => 'C',
    ]);

    $plusPlusCategory = Category::query()->create([
        'name' => 'C++',
    ]);

    expect($plainCategory->slug)->toBe('c')
        ->and($plusPlusCategory->slug)->toBe('c-plus-plus');
});

it('adds a numeric suffix when the normalized category slug already exists', function () {
    Category::query()->create([
        'name' => 'C++',
    ]);

    $duplicateCategory = Category::query()->create([
        'name' => 'C Plus Plus',
    ]);

    expect($duplicateCategory->slug)->toBe('c-plus-plus-2');
});

it('applies the same symbol-aware slug normalization to books, authors, and publishers', function () {
    $publisher = Publisher::query()->create([
        'name' => 'C++ Media',
    ]);

    $author = Author::query()->create([
        'name' => 'C# Expert',
    ]);

    $book = Book::query()->create([
        'title' => 'C++ Primer',
        'publisher_id' => $publisher->getKey(),
    ]);

    expect($publisher->slug)->toBe('c-plus-plus-media')
        ->and($author->slug)->toBe('c-sharp-expert')
        ->and($book->slug)->toBe('c-plus-plus-primer');
});

it('adds a numeric suffix when normalized book slugs collide', function () {
    $publisher = Publisher::factory()->create();

    Book::query()->create([
        'title' => 'C++ Primer',
        'publisher_id' => $publisher->getKey(),
    ]);

    $duplicateBook = Book::query()->create([
        'title' => 'C Plus Plus Primer',
        'publisher_id' => $publisher->getKey(),
    ]);

    expect($duplicateBook->slug)->toBe('c-plus-plus-primer-2');
});
