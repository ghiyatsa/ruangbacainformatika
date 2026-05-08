<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

use function Pest\Laravel\get;

it('home page shows published books from the catalog', function () {

    $author = Author::factory()->create(['name' => 'Andrea Hirata']);
    $category = Category::factory()->create(['name' => 'Novel']);

    $publishedBook = Book::factory()
        ->featured()
        ->create(['title' => 'Laskar Pelangi']);

    $publishedBook->authors()->attach($author);
    $publishedBook->categories()->attach($category);

    BookItem::factory()->available()->create(['book_id' => $publishedBook->id]);

    Book::factory()->unpublished()->create();

    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome')
                ->where('canRegister', Features::enabled(Features::registration()))
                ->where('stats.booksCount', 1)
                ->where('stats.featuredCount', 1)
                ->where('stats.availableItemsCount', 1)
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('featuredBooks', 1)
                        ->where('featuredBooks.0.title', 'Laskar Pelangi')
                        ->has('books.data', 1)
                        ->where('books.data.0.title', 'Laskar Pelangi')
                        ->where('books.data.0.authors.0', 'Andrea Hirata')
                        ->where('books.data.0.categories.0.name', 'Novel')
                        ->where('books.data.0.isAvailable', true)
                ),
        );
});

it('home page does not expose search filtering', function () {
    Book::factory()->published()->create(['title' => 'Atomic Habits']);

    get(route('home', ['search' => 'Atomic']))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome')
                ->where('filters.search', ''),
        );
});

it('home page excludes non-borrowable books from available counts', function () {
    $book = Book::factory()
        ->published()
        ->featured()
        ->nonBorrowable()
        ->create(['title' => 'Ensiklopedia Arsip']);

    BookItem::factory()->available()->create(['book_id' => $book->id]);

    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome')
                ->where('stats.availableItemsCount', 0)
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('featuredBooks', 1)
                        ->where('featuredBooks.0.title', 'Ensiklopedia Arsip')
                        ->where('featuredBooks.0.availableItemsCount', 0)
                        ->where('featuredBooks.0.isAvailable', false)
                ),
        );
});
