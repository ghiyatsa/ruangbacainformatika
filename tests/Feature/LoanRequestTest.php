<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanDraft;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Spatie\Permission\Models\Role;

it('members can add books to a loan request and generate a qr draft', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create();
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Draft',
        'slug' => 'penerbit-draft',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Draft QR',
        'slug' => 'buku-draft-qr',
        'isbn' => '9786020000201',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-DRAFT-001',
        'status' => 'available',
    ]);

    $this->actingAs($member)
        ->post(route('loans.request.books.store'), [
            'book_id' => $book->id,
        ])
        ->assertRedirect();

    $response = $this->actingAs($member)
        ->post(route('loans.request.qr'));

    $response
        ->assertRedirect(route('loans.request'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'QR peminjaman berhasil dibuat. Silakan tunjukkan ke kiosk lobi sebelum masa berlakunya habis.');

    $draft = LoanDraft::query()
        ->whereBelongsTo($member)
        ->latest('id')
        ->first();

    expect($draft)->not->toBeNull()
        ->and($draft->status)->toBe(LoanDraft::STATUS_PENDING)
        ->and($draft->token_hash)->not->toBeNull()
        ->and($draft->items()->count())->toBe(1)
        ->and(Loan::query()->count())->toBe(0)
        ->and(session('loan_request_qr.payload'))->toBeString();
});

it('legacy users without member role can still add books to the cart', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $user = User::factory()->create();

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Legacy',
        'slug' => 'penerbit-legacy',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Legacy',
        'slug' => 'buku-legacy',
        'isbn' => '9786020000204',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-LEGACY-001',
        'status' => 'available',
    ]);

    $this->actingAs($user)
        ->post(route('loans.request.books.store'), [
            'book_id' => $book->id,
        ])
        ->assertRedirect();

    expect($user->fresh()->hasRole('member'))->toBeTrue();

    $draft = LoanDraft::query()->whereBelongsTo($user)->latest('id')->first();

    expect($draft)->not->toBeNull()
        ->and($draft->items()->count())->toBe(1);
});

it('users can add books to cart before profile is complete but cannot generate qr yet', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => null,
        'address' => null,
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Profile',
        'slug' => 'penerbit-profile',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Profile',
        'slug' => 'buku-profile',
        'isbn' => '9786020000205',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-PROFILE-001',
        'status' => 'available',
    ]);

    $this->actingAs($member)
        ->post(route('loans.request.books.store'), [
            'book_id' => $book->id,
        ])
        ->assertRedirect();

    $this->actingAs($member)
        ->from(route('loans.request'))
        ->post(route('loans.request.qr'))
        ->assertRedirect(route('loans.request'))
        ->assertSessionHasErrors('draft');
});
