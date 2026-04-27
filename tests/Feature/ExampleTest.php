<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

test('home page shows published books from the catalog', function () {
    $author = Author::factory()->create(['name' => 'Andrea Hirata']);
    $category = Category::factory()->create(['name' => 'Novel']);

    $publishedBook = Book::factory()
        ->featured()
        ->create(['title' => 'Laskar Pelangi']);

    $publishedBook->authors()->attach($author);
    $publishedBook->categories()->attach($category);

    BookItem::factory()->available()->create(['book_id' => $publishedBook->id]);

    Book::factory()->unpublished()->create();

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('welcome')
        ->where('canRegister', Features::enabled(Features::registration()))
        ->where('stats.booksCount', 1)
        ->where('stats.featuredCount', 1)
        ->where('stats.availableItemsCount', 1)
        ->where('featuredBook.title', 'Laskar Pelangi')
        ->has('books.data', 1)
        ->where('books.data.0.title', 'Laskar Pelangi')
        ->where('books.data.0.authors.0', 'Andrea Hirata')
        ->where('books.data.0.categories.0', 'Novel')
        ->where('books.data.0.isAvailable', true),
    );
});

test('home page does not expose search filtering', function () {
    Book::factory()->published()->create(['title' => 'Atomic Habits']);

    $response = $this->get(route('home', ['search' => 'Atomic']));

    // Home page ignores search param – still shows all books
    $response->assertInertia(fn (Assert $page) => $page
        ->component('welcome')
        ->where('filters.search', ''),
    );
});
