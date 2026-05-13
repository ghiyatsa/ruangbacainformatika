<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\LoanItem;
use App\Models\Publisher;

it('blocks deleting a book when one of its copies has loan history', function () {
    $book = Book::factory()->create();
    $bookItem = BookItem::factory()->for($book)->create();

    LoanItem::factory()->for($bookItem)->create();

    expect($book->fresh()->canBeDeleted())->toBeFalse()
        ->and($book->fresh()->deletionBlockedReason())->not->toBeNull();
});

it('blocks deleting a book copy when it already has loan history', function () {
    $bookItem = BookItem::factory()->create();

    LoanItem::factory()->for($bookItem)->create();

    expect($bookItem->fresh()->canBeDeleted())->toBeFalse()
        ->and($bookItem->fresh()->deletionBlockedReason())->not->toBeNull();
});

it('blocks deleting an author that is still linked to books', function () {
    $author = Author::factory()->create();
    $book = Book::factory()->create();

    $book->authors()->attach($author);

    expect($author->fresh()->deletionBlockedReason())->not->toBeNull();
});

it('blocks deleting a category that is still linked to books', function () {
    $category = Category::factory()->create();
    $book = Book::factory()->create();

    $book->categories()->attach($category);

    expect($category->fresh()->deletionBlockedReason())->not->toBeNull();
});

it('blocks deleting a publisher that is still linked to books', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->for($publisher)->create();

    expect($publisher->fresh()->deletionBlockedReason())->not->toBeNull();
});
