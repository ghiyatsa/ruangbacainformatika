<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\LoanDraft;
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
            ->where('loanRequest', null)
            ->where('loanRequestCart', null)
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
