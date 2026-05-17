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

it('home page previews the newest books instead of prioritizing featured books', function () {
    Book::factory()
        ->published()
        ->featured()
        ->create([
            'title' => 'Buku Unggulan Lama',
            'created_at' => now()->subWeek(),
            'updated_at' => now()->subWeek(),
        ]);

    Book::factory()
        ->published()
        ->create([
            'title' => 'Buku Terbaru',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->where('books.data.0.title', 'Buku Terbaru')
                        ->where('books.data.1.title', 'Buku Unggulan Lama')
                ),
        );
});

it('home page exposes popular categories and most viewed books', function () {
    $artificialIntelligence = Category::factory()->create([
        'name' => 'Kecerdasan Buatan',
        'description' => 'Pembelajaran mesin dan sistem cerdas.',
    ]);

    $networking = Category::factory()->create([
        'name' => 'Jaringan Komputer',
    ]);

    $mostViewedBook = Book::factory()
        ->published()
        ->create([
            'title' => 'Deep Learning Praktis',
            'view_count' => 42,
        ]);

    $lessViewedBook = Book::factory()
        ->published()
        ->create([
            'title' => 'Dasar Jaringan',
            'view_count' => 12,
        ]);

    Book::factory()
        ->unpublished()
        ->create([
            'title' => 'Draft Tidak Tampil',
            'view_count' => 99,
        ]);

    $mostViewedBook->categories()->attach($artificialIntelligence);
    $lessViewedBook->categories()->attach($networking);

    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome')
                ->has('categories', 2)
                ->where('categories.0.name', 'Jaringan Komputer')
                ->where('categories.0.booksCount', 1)
                ->where('categories.1.name', 'Kecerdasan Buatan')
                ->where('categories.1.description', 'Pembelajaran mesin dan sistem cerdas.')
                ->where('categories.1.booksCount', 1)
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('popularBooks', 2)
                        ->where('popularBooks.0.title', 'Deep Learning Praktis')
                        ->where('popularBooks.0.viewCount', 42)
                        ->where('popularBooks.1.title', 'Dasar Jaringan')
                ),
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

it('home page exposes zeroed stats when the catalog is empty', function () {
    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome')
                ->where('stats.booksCount', 0)
                ->where('stats.featuredCount', 0)
                ->where('stats.availableItemsCount', 0),
        );
});
