<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Publisher;
use App\Models\User;
use App\Notifications\LoanReceiptNotification;
use App\Services\KioskLoanService;
use App\Services\KioskPinManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\instance;

it('members must fill whatsapp before borrowing books', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => null,
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
        ->toThrow(ValidationException::class, 'Nomor WhatsApp wajib diisi pada profil sebelum meminjam buku.');
});

it('books marked as not borrowable cannot be borrowed', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
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
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    Notification::fake();

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
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

    $response = $this->post(route('kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'book_ids' => [$book->id],
    ]);

    $response
        ->assertRedirect(route('kiosk.index'))
        ->assertSessionHas('success');

    Notification::assertSentTo($member, LoanReceiptNotification::class);

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

    $response = $this->getJson(route('kiosk.books.search', [
        'q' => 'Laravel',
    ]));

    $response
        ->assertOk()
        ->assertJsonPath('books.0.id', $availableBook->id)
        ->assertJsonPath('books.0.title', $availableBook->title);

    expect(collect($response->json('books'))->pluck('id')->all())
        ->toBe([$availableBook->id]);
});
