<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\User;
use App\Notifications\LoanReceiptNotification;
use App\Notifications\LoanReturnNotification;
use App\Services\KioskLoanService;
use App\Services\KioskPinManager;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\getJson;
use function Pest\Laravel\instance;
use function Pest\Laravel\post;
use function Pest\Laravel\withoutMiddleware;

it('kiosk borrowing rejects partial member identifiers', function () {
    withoutMiddleware(PreventRequestForgery::class);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.loans.borrow'), [
        'member_identifier' => '170020',
        'book_ids' => [1],
    ])
        ->assertSessionHasErrors('member_identifier');
});

it('members must fill whatsapp and address before borrowing books', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => null,
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Test',
        'slug' => 'penerbit-test',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Test',
        'slug' => 'buku-test',
        'isbn' => '9786020000001',
        'publisher_id' => $publisher->id,
        'is_published' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-001',
        'status' => 'available',
    ]);

    $service = app(KioskLoanService::class);

    expect(fn () => $service->borrow($member->nim(), [$book->id]))
        ->toThrow(ValidationException::class, 'Nomor WhatsApp dan alamat wajib diisi pada profil sebelum meminjam buku.');
});

it('books marked as not borrowable cannot be borrowed', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Test',
        'slug' => 'penerbit-test',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Referensi',
        'slug' => 'buku-referensi',
        'isbn' => '9786020000002',
        'issn' => '1234-5678',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => false,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-002',
        'status' => 'available',
    ]);

    $service = app(KioskLoanService::class);

    expect(fn () => $service->borrow($member->nim(), [$book->id]))
        ->toThrow(ValidationException::class, 'Buku Buku Referensi ditandai tidak boleh dipinjam.');
});

it('borrows selected books from kiosk using book ids', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    Notification::fake();

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Test',
        'slug' => 'penerbit-test',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Operasional',
        'slug' => 'buku-operasional',
        'isbn' => '978-602-000-0003',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-003',
        'status' => 'available',
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'book_ids' => [$book->id],
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSessionHas(
            'inertia.flash_data.toast.message',
            "Peminjaman untuk {$member->name} berhasil disimpan. Bukti peminjaman akan dikirim ke WhatsApp anggota.",
        );

    Notification::assertSentTo($member, LoanReceiptNotification::class);

    expect($bookItem->fresh()->status)->toBe('borrowed');
});

it('stores kiosk borrowing even when receipt email dispatch fails', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Dispatch Error',
        'slug' => 'penerbit-dispatch-error',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Dispatch Error',
        'slug' => 'buku-dispatch-error',
        'isbn' => '9786020000099',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-099',
        'status' => 'available',
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $dispatcher = mock(Dispatcher::class);
    $dispatcher->shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('Daily limit exceeded by mail provider.'));
    app()->instance(Dispatcher::class, $dispatcher);

    post(route('kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'book_ids' => [$book->id],
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSessionHas(
            'inertia.flash_data.toast.message',
            "Peminjaman untuk {$member->name} berhasil disimpan. Bukti peminjaman akan dikirim ke WhatsApp anggota.",
        );

    expect($bookItem->fresh()->status)->toBe('borrowed');
});

it('searches borrowable and available books for kiosk borrowing', function () {
    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Search',
        'slug' => 'penerbit-search',
    ]);

    $availableBook = Book::query()->create([
        'title' => 'Pemrograman Laravel Lanjut',
        'slug' => 'pemrograman-laravel-lanjut',
        'isbn' => '9786020000004',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $availableBook->id,
        'internal_code' => 'ITEM-004',
        'status' => 'available',
    ]);

    $unavailableBook = Book::query()->create([
        'title' => 'Pemrograman Laravel Habis',
        'slug' => 'pemrograman-laravel-habis',
        'isbn' => '9786020000005',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $unavailableBook->id,
        'internal_code' => 'ITEM-005',
        'status' => 'borrowed',
    ]);

    $nonBorrowableBook = Book::query()->create([
        'title' => 'Referensi Internal Laravel',
        'slug' => 'referensi-internal-laravel',
        'isbn' => '9786020000006',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => false,
    ]);

    BookItem::query()->create([
        'book_id' => $nonBorrowableBook->id,
        'internal_code' => 'ITEM-006',
        'status' => 'available',
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    getJson(route('kiosk.books.search', [
        'q' => 'Laravel',
    ]))
        ->assertOk()
        ->assertJsonPath('books.0.id', $availableBook->id)
        ->assertJsonPath('books.0.title', $availableBook->title);

    expect(collect(getJson(route('kiosk.books.search', ['q' => 'Laravel']))->json('books'))->pluck('id')->all())
        ->toBe([$availableBook->id]);
});

it('returns selected books from kiosk using book ids', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    Notification::fake();

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Return',
        'slug' => 'penerbit-return',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Pengembalian',
        'slug' => 'buku-pengembalian',
        'isbn' => '9786020000010',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-010',
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    $loanItem = LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $bookItem->id,
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.loans.return'), [
        'member_identifier' => $member->nim(),
        'book_ids' => [$book->id],
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'return']))
        ->assertSessionHas(
            'inertia.flash_data.toast.message',
            '1 buku berhasil dikembalikan.',
        );

    Notification::assertSentTo(
        $member,
        LoanReturnNotification::class,
        fn (LoanReturnNotification $notification): bool => $notification->toArray($member)['book_titles'] === [$book->title]
    );

    expect($loanItem->fresh()->returned_at)->not->toBeNull()
        ->and($bookItem->fresh()->status)->toBe('available')
        ->and($loan->fresh()->status)->toBe(Loan::STATUS_RETURNED);
});

it('searches only active borrowed books for kiosk returns', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $otherMember = User::factory()->create([
        'whatsapp' => '08123456780',
        'whatsapp_verified_at' => now(),
    ]);
    $otherMember->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Return Search',
        'slug' => 'penerbit-return-search',
    ]);

    $borrowedBook = Book::query()->create([
        'title' => 'Laravel Return Aktif',
        'slug' => 'laravel-return-aktif',
        'isbn' => '9786020000011',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $borrowedItem = BookItem::query()->create([
        'book_id' => $borrowedBook->id,
        'internal_code' => 'ITEM-011',
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $borrowedItem->id,
    ]);

    $returnedBook = Book::query()->create([
        'title' => 'Laravel Sudah Kembali',
        'slug' => 'laravel-sudah-kembali',
        'isbn' => '9786020000012',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $returnedItem = BookItem::query()->create([
        'book_id' => $returnedBook->id,
        'internal_code' => 'ITEM-012',
        'status' => 'available',
    ]);

    $returnedLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_RETURNED,
        'borrowed_at' => now()->subDays(5),
        'due_at' => now()->subDays(2),
        'returned_at' => now()->subDay(),
    ]);

    LoanItem::query()->create([
        'loan_id' => $returnedLoan->id,
        'book_item_id' => $returnedItem->id,
        'returned_at' => now()->subDay(),
    ]);

    $otherBook = Book::query()->create([
        'title' => 'Laravel Member Lain',
        'slug' => 'laravel-member-lain',
        'isbn' => '9786020000013',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $otherItem = BookItem::query()->create([
        'book_id' => $otherBook->id,
        'internal_code' => 'ITEM-013',
        'status' => 'borrowed',
    ]);

    $otherLoan = Loan::query()->create([
        'user_id' => $otherMember->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    LoanItem::query()->create([
        'loan_id' => $otherLoan->id,
        'book_item_id' => $otherItem->id,
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    getJson(route('kiosk.books.search', [
        'q' => 'Laravel',
        'mode' => 'return',
        'member_identifier' => $member->nim(),
    ]))
        ->assertOk()
        ->assertJsonPath('books.0.id', $borrowedBook->id)
        ->assertJsonPath('books.0.title', $borrowedBook->title);

    expect(collect(getJson(route('kiosk.books.search', [
        'q' => 'Laravel',
        'mode' => 'return',
        'member_identifier' => $member->nim(),
    ]))->json('books'))->pluck('id')->all())
        ->toBe([$borrowedBook->id]);
});

it('lists active borrowed books for kiosk returns without a search query', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Return Default',
        'slug' => 'penerbit-return-default',
    ]);

    $firstBook = Book::query()->create([
        'title' => 'Buku Pinjaman Pertama',
        'slug' => 'buku-pinjaman-pertama',
        'isbn' => '9786020000101',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $firstItem = BookItem::query()->create([
        'book_id' => $firstBook->id,
        'internal_code' => 'ITEM-101',
        'status' => 'borrowed',
    ]);

    $secondBook = Book::query()->create([
        'title' => 'Buku Pinjaman Kedua',
        'slug' => 'buku-pinjaman-kedua',
        'isbn' => '9786020000102',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $secondItem = BookItem::query()->create([
        'book_id' => $secondBook->id,
        'internal_code' => 'ITEM-102',
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $firstItem->id,
    ]);

    LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $secondItem->id,
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    getJson(route('kiosk.books.search', [
        'mode' => 'return',
        'member_identifier' => $member->nim(),
    ]))
        ->assertOk();

    expect(collect(getJson(route('kiosk.books.search', [
        'mode' => 'return',
        'member_identifier' => $member->nim(),
    ]))->json('books'))->pluck('id')->sort()->values()->all())
        ->toBe(collect([$firstBook->id, $secondBook->id])->sort()->values()->all());
});

it('borrows and returns books using email as identifier', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    Notification::fake();

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Email Test',
        'slug' => 'penerbit-email-test',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Email Test',
        'slug' => 'buku-email-test',
        'isbn' => '9786020000201',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-201',
        'status' => 'available',
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    // Test borrowing with email
    post(route('kiosk.loans.borrow'), [
        'member_identifier' => $member->email,
        'book_ids' => [$book->id],
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']));

    expect($bookItem->fresh()->status)->toBe('borrowed');

    // Test returning with email
    post(route('kiosk.loans.return'), [
        'member_identifier' => $member->email,
        'book_ids' => [$book->id],
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'return']));

    expect($bookItem->fresh()->status)->toBe('available');
});

it('borrows and returns books using phone number as identifier with different prefix formats', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    Notification::fake();

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Phone Test',
        'slug' => 'penerbit-phone-test',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Phone Test',
        'slug' => 'buku-phone-test',
        'isbn' => '9786020000202',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-202',
        'status' => 'available',
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    // Test borrowing with phone number containing spaces/dashes/+ prefix
    post(route('kiosk.loans.borrow'), [
        'member_identifier' => '+62 812-3456-789',
        'book_ids' => [$book->id],
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']));

    expect($bookItem->fresh()->status)->toBe('borrowed');

    // Test returning with phone number starting with 08
    post(route('kiosk.loans.return'), [
        'member_identifier' => '08123456789',
        'book_ids' => [$book->id],
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'return']));

    expect($bookItem->fresh()->status)->toBe('available');
});
