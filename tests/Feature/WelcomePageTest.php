<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\LoanItem;
use App\Models\Setting;
use Inertia\Testing\AssertableInertia as Assert;

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
                ->component('welcome/index')
                ->where('stats.booksCount', 1)
                ->where('stats.featuredCount', 1)
                ->where('stats.availableItemsCount', 1)
                ->where('stats.activeCategoriesCount', 1)
                ->reloadOnly(['featuredBooks', 'books'], fn (Assert $reload) => $reload
                    ->has('featuredBooks', 1)
                    ->where('featuredBooks.0.title', 'Laskar Pelangi')
                    ->has('books.data', 1)
                    ->where('books.data.0.title', 'Laskar Pelangi')
                    ->where('books.data.0.authors.0', 'Andrea Hirata')
                    ->where('books.data.0.categories.0.name', 'Novel')
                    ->where('books.data.0.isAvailable', true)
                )
        );
});

it('home page does not expose search filtering', function () {
    Book::factory()->published()->create(['title' => 'Atomic Habits']);

    get(route('home', ['search' => 'Atomic']))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome/index')
                ->missing('filters'),
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
                ->component('welcome/index')
                ->reloadOnly('books', fn (Assert $reload) => $reload
                    ->where('books.data.0.title', 'Buku Terbaru')
                    ->where('books.data.1.title', 'Buku Unggulan Lama')
                )
        );
});

it('home page exposes top popular-category shelves and most viewed books', function () {
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
                ->component('welcome/index')
                ->where('stats.activeCategoriesCount', 2)
                ->reloadOnly(['popularBooks', 'popularCategoryShelves'], fn (Assert $reload) => $reload
                    ->has('popularBooks', 2)
                    ->where('popularBooks.0.title', 'Deep Learning Praktis')
                    ->where('popularBooks.0.viewCount', 42)
                    ->where('popularBooks.1.title', 'Dasar Jaringan')
                    ->has('popularCategoryShelves', 2)
                    ->where('popularCategoryShelves.0.name', 'Jaringan Komputer')
                    ->where('popularCategoryShelves.0.booksCount', 1)
                    ->where('popularCategoryShelves.0.books.0.title', 'Dasar Jaringan')
                    ->where('popularCategoryShelves.1.name', 'Kecerdasan Buatan')
                    ->where('popularCategoryShelves.1.description', 'Pembelajaran mesin dan sistem cerdas.')
                    ->where('popularCategoryShelves.1.booksCount', 1)
                    ->where('popularCategoryShelves.1.books.0.title', 'Deep Learning Praktis')
                )
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
                ->component('welcome/index')
                ->where('stats.availableItemsCount', 0)
                ->where('stats.activeCategoriesCount', 0)
                ->reloadOnly('featuredBooks', fn (Assert $reload) => $reload
                    ->has('featuredBooks', 1)
                    ->where('featuredBooks.0.title', 'Ensiklopedia Arsip')
                    ->where('featuredBooks.0.availableItemsCount', 0)
                    ->where('featuredBooks.0.isAvailable', false)
                )
        );
});

it('home page exposes books ordered by the most borrowing history', function () {
    $mostBorrowedBook = Book::factory()
        ->published()
        ->create(['title' => 'Algoritma Lanjut']);

    $lessBorrowedBook = Book::factory()
        ->published()
        ->create(['title' => 'Basis Data Praktis']);

    $neverBorrowedBook = Book::factory()
        ->published()
        ->create(['title' => 'Pemrograman Web Dasar']);

    foreach (range(1, 3) as $index) {
        $bookItem = BookItem::factory()->borrowed()->create([
            'book_id' => $mostBorrowedBook->id,
            'internal_code' => "ALG-{$index}",
        ]);

        LoanItem::factory()->for($bookItem)->create();
    }

    $lessBorrowedItem = BookItem::factory()->borrowed()->create([
        'book_id' => $lessBorrowedBook->id,
        'internal_code' => 'DB-1',
    ]);

    LoanItem::factory()->for($lessBorrowedItem)->create();

    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome/index')
                ->reloadOnly('mostBorrowedBooks', fn (Assert $reload) => $reload
                    ->has('mostBorrowedBooks', 2)
                    ->where('mostBorrowedBooks.0.title', 'Algoritma Lanjut')
                    ->where('mostBorrowedBooks.1.title', 'Basis Data Praktis')
                )
        );
});

it('home page exposes zeroed stats when the catalog is empty', function () {
    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome/index')
                ->where('stats.booksCount', 0)
                ->where('stats.featuredCount', 0)
                ->where('stats.availableItemsCount', 0)
                ->where('stats.activeCategoriesCount', 0),
        );
});

it('home page limits popular category shelves to the top three categories', function () {
    foreach (range(1, 30) as $number) {
        $category = Category::factory()->create([
            'name' => sprintf('Kategori %02d', $number),
            'slug' => sprintf('kategori-%02d', $number),
        ]);

        $book = Book::factory()
            ->published()
            ->create(['title' => sprintf('Buku %02d', $number)]);

        $book->categories()->attach($category);
    }

    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome/index')
                ->where('stats.activeCategoriesCount', 30)
                ->reloadOnly('popularCategoryShelves', fn (Assert $reload) => $reload
                    ->has('popularCategoryShelves', 3)
                    ->where('popularCategoryShelves.0.name', 'Kategori 01')
                    ->where('popularCategoryShelves.0.books.0.title', 'Buku 01')
                )
        );
});

it('home page shares the hero notice from general settings', function () {
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'hero_notice_enabled'],
        ['value' => '1'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'hero_notice_text'],
        ['value' => 'Layanan perpustakaan tutup pada Jumat, 30 Mei 2026.'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'hero_notice_url'],
        ['value' => 'https://example.com/pengumuman'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'hero_notice_link_label'],
        ['value' => 'Baca pengumuman'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'hero_notice_tone'],
        ['value' => 'warning'],
    );

    get(route('home'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('welcome/index')
                ->where('site.notice.isActive', true)
                ->where('site.notice.text', 'Layanan perpustakaan tutup pada Jumat, 30 Mei 2026.')
                ->where('site.notice.url', 'https://example.com/pengumuman')
                ->where('site.notice.linkLabel', 'Baca pengumuman')
                ->where('site.notice.tone', 'warning'),
        );
});
