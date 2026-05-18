<?php

use App\Models\Book;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('book detail page renders correctly', function () {
    $book = Book::factory()->published()->create([
        'title' => 'Test Book',
        'view_count' => 0,
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('book.data.title', 'Test Book')
            ->where('book.data.viewCount', 1)
        );

    expect($book->fresh()->view_count)->toBe(1);
});

test('book detail page increments view count', function () {
    $book = Book::factory()->published()->create(['view_count' => 5]);

    get(route('books.show', $book));
    get(route('books.show', $book));

    expect($book->fresh()->view_count)->toBe(7);
});

test('unpublished book detail page returns 404', function () {
    $book = Book::factory()->unpublished()->create();

    get(route('books.show', $book))
        ->assertNotFound();
});

test('book editor state is persisted as structured data', function () {
    $book = Book::factory()->create([
        'cover_image_editor_state' => [
            'x' => 12,
            'y' => 8,
            'zoom' => 1.2,
        ],
    ]);

    expect($book->fresh()->cover_image_editor_state)
        ->toBe([
            'x' => 12,
            'y' => 8,
            'zoom' => 1.2,
        ]);
});
