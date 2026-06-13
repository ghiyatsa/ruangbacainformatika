<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('catalog page renders published books', function () {
    $book = Book::factory()->published()->create(['title' => 'Clean Code']);
    BookItem::factory()->available()->create(['book_id' => $book->id]);

    Book::factory()->unpublished()->create();

    get(route('books.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('books/index')
                ->loadDeferredProps(fn (Assert $reload) => $reload
                    ->has('books.data', 1)
                    ->where('books.data.0.title', 'Clean Code')
                    ->where('books.data.0.isAvailable', true)
                ),
        );
});

it('catalog page search filters by title', function () {
    $matching = Book::factory()->published()->create(['title' => 'The Pragmatic Programmer']);
    Book::factory()->published()->create(['title' => 'Clean Architecture']);

    get(route('books.index', ['search' => 'Pragmatic']))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('books/index')
                ->where('filters.search', 'Pragmatic')
                ->loadDeferredProps(fn (Assert $reload) => $reload
                    ->has('books.data', 1)
                    ->where('books.data.0.title', 'The Pragmatic Programmer')
                ),
        );
});

it('catalog page search filters by author', function () {
    $author = Author::factory()->create(['name' => 'Robert Martin']);
    $book = Book::factory()->published()->create(['title' => 'Clean Code']);
    $book->authors()->attach($author);

    Book::factory()->published()->create(['title' => 'Other Book']);

    get(route('books.index', ['search' => 'Robert Martin']))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('books/index')
                ->loadDeferredProps(fn (Assert $reload) => $reload
                    ->has('books.data', 1)
                    ->where('books.data.0.title', 'Clean Code')
                ),
        );
});

it('catalog page filters by category', function () {
    $category = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);
    $book = Book::factory()->published()->create(['title' => 'Laravel Book']);
    $book->categories()->attach($category);

    Book::factory()->published()->create(['title' => 'Other Book']);

    get(route('books.index', ['category' => 'programming']))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('books/index')
                ->where('filters.category', 'programming')
                ->loadDeferredProps(fn (Assert $reload) => $reload
                    ->has('books.data', 1)
                    ->where('books.data.0.title', 'Laravel Book')
                ),
        );
});

it('catalog page returns empty results for no match', function () {
    Book::factory()->published()->create(['title' => 'Laravel Book']);

    get(route('books.index', ['search' => 'xyznonexistent999']))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('books/index')
                ->where('stats.searchResultsCount', 0)
                ->loadDeferredProps(fn (Assert $reload) => $reload
                    ->has('books.data', 0)
                ),
        );
});

it('catalog stats include books count and available items count', function () {
    $book = Book::factory()->published()->create();
    BookItem::factory()->available()->create(['book_id' => $book->id]);
    BookItem::factory()->borrowed()->create(['book_id' => $book->id]);

    get(route('books.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('books/index')
                ->where('stats.booksCount', 1)
                ->where('stats.availableItemsCount', 1),
        );
});

it('catalog availability excludes books marked as not borrowable', function () {
    $book = Book::factory()
        ->published()
        ->nonBorrowable()
        ->create(['title' => 'Reference Only']);

    BookItem::factory()->available()->create(['book_id' => $book->id]);

    get(route('books.index'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('books/index')
                ->where('stats.availableItemsCount', 0)
                ->loadDeferredProps(fn (Assert $reload) => $reload
                    ->where('books.data.0.title', 'Reference Only')
                    ->where('books.data.0.availableItemsCount', 0)
                    ->where('books.data.0.isAvailable', false)
                ),
        );
});

it('catalog page returns the requested pagination page', function () {
    foreach (range(1, 25) as $number) {
        Book::factory()->published()->create([
            'title' => sprintf('Book %02d', $number),
            'published_year' => 2024,
            'is_featured' => false,
        ]);
    }

    get(route('books.index', ['page' => 2]))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('books/index')
                ->where('stats.searchResultsCount', 25)
                ->loadDeferredProps(fn (Assert $reload) => $reload
                    ->where('books.current_page', 2)
                    ->whereNot('books.next_page_url', null)
                    ->has('books.data', 12)
                    ->where('books.data.0.title', 'Book 13')
                ),
        );
});
