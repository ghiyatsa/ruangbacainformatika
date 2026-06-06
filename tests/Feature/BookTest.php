<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\LoanDraft;
use App\Models\Publisher;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
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

    $relatedBook = Book::factory()->published()->create([
        'title' => 'Pemrograman Web Praktis',
        'publisher_id' => $publisher->id,
        'published_year' => 2024,
    ]);
    $relatedBook->authors()->attach($sharedAuthor);
    $relatedBook->categories()->attach($sharedCategory);

    Book::factory()->published()->create([
        'title' => 'Fisika Dasar',
        'published_year' => 2018,
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('relatedBooks')
                ->where('relatedBooks.0.id', $relatedBook->id)
            ));
});

it('book detail page shares loan request summary for authenticated users', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'whatsapp_verified_at' => now(),
    ]);
    $user->assignRole('member');

    $book = Book::factory()->published()->create([
        'title' => 'Borrowable Book',
        'is_borrowable' => true,
    ]);

    BookItem::factory()->available()->create(['book_id' => $book->id]);

    $draft = LoanDraft::query()->create([
        'user_id' => $user->id,
        'status' => LoanDraft::STATUS_PENDING,
    ]);

    $draft->items()->create([
        'book_id' => $book->id,
    ]);

    /** @var User $user */
    actingAs($user);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('loanRequest.count', 1)
            ->where('loanRequest.containsBook', true)
            ->where('loanRequestCart.count', 1)
        );
});

it('book detail page hides loan request summary for authenticated users without borrowing access', function () {
    $user = User::factory()->create([
        'email' => 'outside@example.com',
        'is_approved' => false,
    ]);

    $book = Book::factory()->published()->create([
        'title' => 'Public Book',
        'is_borrowable' => true,
    ]);

    /** @var User $user */
    actingAs($user);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('auth.canBorrowBooks', false)
            ->where('auth.borrowingAccessMessage', 'Layanan peminjaman tersedia setelah status anggota Anda lengkap.')
            ->where('loanRequest', null)
            ->where('loanRequestCart', null)
        );
});

it('book detail page explains manual approval status for campus users who still cannot borrow', function () {
    $user = User::factory()->create([
        'email' => 'dosen@unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'is_approved' => false,
    ]);

    $book = Book::factory()->published()->create([
        'title' => 'Campus Book',
        'is_borrowable' => true,
    ]);

    /** @var User $user */
    actingAs($user);

    get(route('books.show', $book))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('books/show')
            ->where('auth.canBorrowBooks', false)
            ->where('auth.borrowingAccessMessage', 'Akun kampus Anda sedang menunggu persetujuan admin.')
        );
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
