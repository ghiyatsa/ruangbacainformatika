<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanDraft;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\withoutMiddleware;

it('members can add books to a loan request and generate a qr draft', function () {
    withoutMiddleware(PreventRequestForgery::class);

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

    /** @var User $member */
    actingAs($member)
        ->post(route('loans.request.books.store'), [
            'book_id' => $book->id,
        ])
        ->assertRedirect();

    /** @var User $member */
    actingAs($member)
        ->post(route('loans.request.qr'), [
            'book_ids' => [$book->id],
        ])
        ->assertRedirect(route('loans.request'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'QR berhasil dibuat.');

    $draft = LoanDraft::query()
        ->whereBelongsTo($member)
        ->latest('id')
        ->first();

    expect($draft)->toBeInstanceOf(LoanDraft::class);
    expect($draft->status)->toBe(LoanDraft::STATUS_PENDING);
    expect($draft->token_hash)->not->toBeNull();
    expect($draft->items()->count())->toBe(1);
    assertDatabaseCount('loans', 0);
    expect(session('loan_request_qr.payload'))->toBeString();

    /** @var User $member */
    actingAs($member)
        ->get(route('loans.request'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('loans/request')
            ->where('draft.hasActiveQr', true)
            ->where('draft.qrCodeSvg', fn (?string $value): bool => filled($value))
            ->where('draft.expiresAtIso', fn (?string $value): bool => filled($value))
            ->missing('draft.qrPayload')
        );
});

it('public users without teknik informatika borrowing access cannot add books to the cart', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'email' => '230160001@mhs.unimal.ac.id',
        'is_approved' => false,
    ]);

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

    /** @var User $user */
    actingAs($user)
        ->post(route('loans.request.books.store'), [
            'book_id' => $book->id,
        ])
        ->assertSessionHasErrors([
            'draft' => 'Layanan peminjaman tersedia untuk mahasiswa Teknik Informatika yang terdaftar.',
        ]);

    expect($user->fresh()->hasRole('member'))->toBeFalse();

    $draft = LoanDraft::query()->whereBelongsTo($user)->latest('id')->first();

    expect($draft)->toBeNull();
});

it('authenticated public users are redirected away from the loan request page', function () {
    $user = User::factory()->create([
        'email' => 'outside@example.com',
        'is_approved' => false,
    ]);

    /** @var User $user */
    actingAs($user)
        ->get(route('loans.request'))
        ->assertRedirect(route('home'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'Layanan peminjaman tersedia untuk mahasiswa Teknik Informatika yang terdaftar.');
});

it('users can add books to cart before profile is complete but cannot generate qr yet', function () {
    withoutMiddleware(PreventRequestForgery::class);

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

    /** @var User $member */
    actingAs($member)
        ->post(route('loans.request.books.store'), [
            'book_id' => $book->id,
        ])
        ->assertRedirect();

    /** @var User $member */
    actingAs($member)
        ->from(route('loans.request'))
        ->post(route('loans.request.qr'), [
            'book_ids' => [$book->id],
        ])
        ->assertRedirect(route('loans.request'))
        ->assertSessionHasErrors('draft');
});

it('members can add more books to cart and generate qr for a subset within remaining quota', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '081234567890',
        'address' => 'Jl. Kampus',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Limit',
        'slug' => 'penerbit-limit',
    ]);

    $books = collect(range(1, 4))->map(function (int $number) use ($publisher) {
        $book = Book::query()->create([
            'title' => "Buku Limit {$number}",
            'slug' => "buku-limit-{$number}",
            'isbn' => '97860200003'.$number,
            'publisher_id' => $publisher->id,
            'is_published' => true,
            'is_borrowable' => true,
        ]);

        BookItem::query()->create([
            'book_id' => $book->id,
            'internal_code' => 'ITEM-LIMIT-00'.$number,
            'status' => 'available',
        ]);

        return $book;
    });

    /** @var User $member */
    foreach ($books as $book) {
        actingAs($member)
            ->post(route('loans.request.books.store'), [
                'book_id' => $book->id,
            ])
            ->assertRedirect();
    }

    $draft = LoanDraft::query()
        ->whereBelongsTo($member)
        ->latest('id')
        ->first();

    expect($draft)->toBeInstanceOf(LoanDraft::class);
    expect($draft->items()->count())->toBe(4);

    /** @var User $member */
    actingAs($member)
        ->post(route('loans.request.qr'), [
            'book_ids' => $books->take(3)->pluck('id')->all(),
        ])
        ->assertRedirect(route('loans.request'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'QR berhasil dibuat.');

    $draft->refresh();

    expect($draft->selected_book_ids)->toBe($books->take(3)->pluck('id')->all());
});

it('members cannot generate qr for more books than the remaining quota', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '081234567890',
        'address' => 'Jl. Kampus',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Overquota',
        'slug' => 'penerbit-overquota',
    ]);

    $books = collect(range(1, 4))->map(function (int $number) use ($publisher) {
        $book = Book::query()->create([
            'title' => "Buku Overquota {$number}",
            'slug' => "buku-overquota-{$number}",
            'isbn' => '97860200004'.$number,
            'publisher_id' => $publisher->id,
            'is_published' => true,
            'is_borrowable' => true,
        ]);

        BookItem::query()->create([
            'book_id' => $book->id,
            'internal_code' => 'ITEM-OVERQUOTA-00'.$number,
            'status' => 'available',
        ]);

        return $book;
    });

    /** @var User $member */
    foreach ($books as $book) {
        actingAs($member)
            ->post(route('loans.request.books.store'), [
                'book_id' => $book->id,
            ])
            ->assertRedirect();
    }

    /** @var User $member */
    actingAs($member)
        ->from(route('loans.request'))
        ->post(route('loans.request.qr'), [
            'book_ids' => $books->pluck('id')->all(),
        ])
        ->assertRedirect(route('loans.request'))
        ->assertSessionHasErrors([
            'book_ids' => 'Anda hanya dapat memilih maksimal 3 buku untuk QR ini.',
        ]);
});

it('members cannot generate a new qr while the current qr is still active', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '081234567890',
        'address' => 'Jl. Kampus',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Active QR',
        'slug' => 'penerbit-active-qr',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku QR Aktif',
        'slug' => 'buku-qr-aktif',
        'isbn' => '9786020000999',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-ACTIVE-QR-001',
        'status' => 'available',
    ]);

    $draft = LoanDraft::query()->create([
        'user_id' => $member->id,
        'status' => LoanDraft::STATUS_PENDING,
        'token_hash' => hash('sha256', 'RB-LOAN-ACTIVE-TOKEN'),
        'expires_at' => now()->addMinutes(5),
        'selected_book_ids' => [$book->id],
    ]);

    $draft->items()->create([
        'book_id' => $book->id,
    ]);

    /** @var User $member */
    actingAs($member)
        ->from(route('loans.request'))
        ->post(route('loans.request.qr'), [
            'book_ids' => [$book->id],
        ])
        ->assertRedirect(route('loans.request'))
        ->assertSessionHasErrors([
            'draft' => 'QR masih aktif. Tunggu hingga masa berlakunya berakhir.',
        ]);
});

it('members cannot generate qr while borrowing access is suspended because of an overdue loan', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_suspension_enabled'],
        ['value' => '1'],
    );

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_suspend_after_days'],
        ['value' => '1'],
    );

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_cooldown_days'],
        ['value' => '3'],
    );

    $member = User::factory()->create([
        'whatsapp' => '081234567892',
        'address' => 'Jl. Kampus Baru',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Suspended',
        'slug' => 'penerbit-suspended',
    ]);

    $overdueBook = Book::query()->create([
        'title' => 'Buku Terlambat Aktif',
        'slug' => 'buku-terlambat-aktif',
        'isbn' => '9786020000210',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $overdueItem = BookItem::query()->create([
        'book_id' => $overdueBook->id,
        'internal_code' => 'ITEM-OVERDUE-ACTIVE',
        'status' => 'borrowed',
    ]);

    $overdueLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDays(6),
        'due_at' => now()->subDays(2),
    ]);

    LoanItem::query()->create([
        'loan_id' => $overdueLoan->id,
        'book_item_id' => $overdueItem->id,
    ]);

    $draftBook = Book::query()->create([
        'title' => 'Buku Draft Suspended',
        'slug' => 'buku-draft-suspended',
        'isbn' => '9786020000211',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $draftBook->id,
        'internal_code' => 'ITEM-DRAFT-SUSPENDED',
        'status' => 'available',
    ]);

    actingAs($member)
        ->post(route('loans.request.books.store'), [
            'book_id' => $draftBook->id,
        ])
        ->assertRedirect();

    actingAs($member)
        ->from(route('loans.request'))
        ->post(route('loans.request.qr'), [
            'book_ids' => [$draftBook->id],
        ])
        ->assertRedirect(route('loans.request'))
        ->assertSessionHasErrors([
            'draft' => 'Akun ini sedang dibatasi karena memiliki pinjaman yang terlambat 2 hari. Kembalikan seluruh buku yang melewati batas pengembalian untuk meminjam lagi.',
        ]);
});
