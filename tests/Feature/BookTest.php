<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\Publisher;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('book detail page renders correctly', function () {
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

it('book detail page renders correctly with detailed authors and publisher data', function () {
    $author = Author::factory()->create(['name' => 'John Doe', 'slug' => 'john-doe']);
    $publisher = Publisher::factory()->create(['name' => 'Acme Publishing', 'slug' => 'acme-publishing']);
    $book = Book::factory()->published()->create([
        'title' => 'Test Book',
        'publisher_id' => $publisher->id,
    ]);
    $book->authors()->attach($author);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('book.data.title', 'Test Book')
            ->where('book.data.authorsData.0.name', 'John Doe')
            ->where('book.data.authorsData.0.slug', 'john-doe')
            ->where('book.data.publisherData.name', 'Acme Publishing')
            ->where('book.data.publisherData.slug', 'acme-publishing')
        );
});

it('book detail page shares issn-specific publication details for journals', function () {
    $book = Book::factory()->published()->create([
        'title' => 'Jurnal Informatika',
        'isbn' => null,
        'issn' => '1234-5678',
        'edition' => 'Vol. 12 No. 2',
        'pages' => '120-145',
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('book.data.isbn', null)
            ->where('book.data.issn', '1234-5678')
            ->where('book.data.edition', 'Vol. 12 No. 2')
            ->where('book.data.pages', '120-145')
        );
});

it('book detail page keeps serial metadata empty for isbn books', function () {
    $book = Book::factory()->published()->create([
        'title' => 'Clean Code',
        'isbn' => '9780132350884',
        'issn' => null,
        'edition' => null,
        'pages' => null,
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('book.data.isbn', '9780132350884')
            ->where('book.data.issn', null)
            ->where('book.data.edition', null)
            ->where('book.data.pages', null)
        );
});

it('book detail page increments view count', function () {
    $book = Book::factory()->published()->create(['view_count' => 5]);

    get(route('books.show', $book));
    get(route('books.show', $book));

    expect($book->fresh()->view_count)->toBe(7);
});

it('book detail page shares primary shelf locations from the first five displayed copies', function () {
    $book = Book::factory()->published()->create([
        'title' => 'Algoritma Dasar',
    ]);

    foreach (range(1, 6) as $index) {
        BookItem::factory()->create([
            'book_id' => $book->id,
            'internal_code' => sprintf('ALG-%03d', $index),
            'status' => 'available',
            'shelf_location' => $index <= 5 ? 'R-01-A' : 'ARSIP-02',
        ]);
    }

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('book.data.displayShelfLocations', ['R-01-A'])
            ->where('book.data.usesBackupShelfLocations', false)
        );
});

it('book detail page falls back to backup shelf locations when the first five displayed copies are borrowed', function () {
    $book = Book::factory()->published()->create([
        'title' => 'Struktur Data Lanjut',
    ]);

    foreach (range(1, 5) as $index) {
        BookItem::factory()->borrowed()->create([
            'book_id' => $book->id,
            'internal_code' => sprintf('SDL-%03d', $index),
            'shelf_location' => 'R-02-B',
        ]);
    }

    BookItem::factory()->available()->create([
        'book_id' => $book->id,
        'internal_code' => 'SDL-006',
        'shelf_location' => 'CAD-01',
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('book.data.displayShelfLocations', ['CAD-01'])
            ->where('book.data.usesBackupShelfLocations', true)
        );
});

it('book detail page loads related books as deferred props', function () {
    $sharedAuthor = Author::factory()->create();
    $sharedCategory = Category::factory()->create();
    $publisher = Publisher::factory()->create();

    $book = Book::factory()->published()->create([
        'title' => 'Pemrograman Web Lanjut',
        'publisher_id' => $publisher->id,
        'published_year' => 2024,
    ]);
    $book->authors()->attach($sharedAuthor);
    $book->categories()->attach($sharedCategory);

    // Berbagi penulis + penerbit => masuk rekomendasi, harus dikecualikan
    // dari buku terkait agar tidak tampil dua kali.
    $recommendedBook = Book::factory()->published()->create([
        'title' => 'Pemrograman Web Praktis',
        'publisher_id' => $publisher->id,
        'published_year' => 2024,
    ]);
    $recommendedBook->authors()->attach($sharedAuthor);
    $recommendedBook->categories()->attach($sharedCategory);

    $unrelatedBook = Book::factory()->published()->create([
        'title' => 'Fisika Dasar',
        'published_year' => 2018,
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('relatedBooks')
                ->has('recommendedBooks')
                ->where('recommendedBooks.0.id', $recommendedBook->id)
                ->where('relatedBooks.0.id', $unrelatedBook->id)
                ->where('relatedBooks', fn ($related) => collect($related)
                    ->pluck('id')
                    ->doesntContain($recommendedBook->id))
            ));
});

it('book detail page recommends books from the same author', function () {
    $author = Author::factory()->create();

    $book = Book::factory()->published()->create(['title' => 'Buku Utama']);
    $book->authors()->attach($author);

    $sameAuthorBook = Book::factory()->published()->create(['title' => 'Buku Penulis Sama']);
    $sameAuthorBook->authors()->attach($author);

    $otherAuthor = Author::factory()->create();
    $otherBook = Book::factory()->published()->create(['title' => 'Buku Lain']);
    $otherBook->authors()->attach($otherAuthor);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->where('recommendedBooks.0.id', $sameAuthorBook->id)
                ->where('recommendedBooks', fn ($books) => collect($books)
                    ->pluck('id')
                    ->doesntContain($book->id)
                    && collect($books)->pluck('id')->doesntContain($otherBook->id))
            ));
});

it('book detail page recommends books from the same publisher', function () {
    $publisher = Publisher::factory()->create();

    $book = Book::factory()->published()->create([
        'title' => 'Buku Utama',
        'publisher_id' => $publisher->id,
    ]);

    $samePublisherBook = Book::factory()->published()->create([
        'title' => 'Buku Penerbit Sama',
        'publisher_id' => $publisher->id,
    ]);

    $otherPublisher = Publisher::factory()->create();
    $otherBook = Book::factory()->published()->create([
        'title' => 'Buku Penerbit Lain',
        'publisher_id' => $otherPublisher->id,
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->where('recommendedBooks.0.id', $samePublisherBook->id)
                ->where('recommendedBooks', fn ($books) => collect($books)
                    ->pluck('id')
                    ->doesntContain($otherBook->id))
            ));
});

it('book detail page shows no recommendations without a shared author or publisher', function () {
    $book = Book::factory()->published()->create([
        'publisher_id' => Publisher::factory()->create()->id,
    ]);

    Book::factory()->published()->create([
        'publisher_id' => Publisher::factory()->create()->id,
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->where('recommendedBooks', [])
            ));
});

it('book detail page never shows the same book in both related and recommended sections', function () {
    $author = Author::factory()->create();
    $publisher = Publisher::factory()->create();

    $book = Book::factory()->published()->create([
        'publisher_id' => $publisher->id,
        'published_year' => 2024,
    ]);
    $book->authors()->attach($author);

    foreach (range(1, 3) as $index) {
        $twin = Book::factory()->published()->create([
            'title' => "Buku Seri {$index}",
            'publisher_id' => $publisher->id,
            'published_year' => 2024,
        ]);
        $twin->authors()->attach($author);
    }

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page
                ->component('books/show')
                ->loadDeferredProps(function (Assert $reload): void {
                    $relatedIds = collect($reload->toArray()['props']['relatedBooks'] ?? [])->pluck('id');
                    $recommendedIds = collect($reload->toArray()['props']['recommendedBooks'] ?? [])->pluck('id');

                    expect($relatedIds->intersect($recommendedIds))->toBeEmpty()
                        ->and($recommendedIds)->not->toBeEmpty();
                });
        });
});

it('unpublished book detail page returns 404', function () {
    $book = Book::factory()->unpublished()->create();

    get(route('books.show', $book))
        ->assertNotFound();
});

it('book editor state is persisted as structured data', function () {
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
