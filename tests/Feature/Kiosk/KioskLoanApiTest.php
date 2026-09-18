<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\KioskDevice;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\Setting;
use App\Models\User;
use App\Services\KioskBorrowVerificationService;
use App\Services\KioskLoanService;
use Carbon\Carbon;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

/**
 * Uji logika peminjaman/pengembalian lewat API kiosk (klien Flutter desktop).
 *
 * Cakupan ini dipindahkan dari `tests/Feature/Kiosk/BorrowBookTest.php` yang
 * menguji route web `/kiosk/*`. Route web sudah dihapus, tetapi Action/Service
 * yang diuji tetap hidup dan dipakai aplikasi desktop — jadi pengamannya
 * harus tetap ada, di endpoint yang benar.
 */
beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
    Carbon::setTestNow('2026-06-07 03:00:00'); // 10:00 WIB — dalam jam operasional

    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'pin_hash',
        'value' => Hash::make('123456'),
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * Helper: aktifkan perangkat kiosk dan kembalikan tokennya.
 */
function borrowApiDeviceToken(): string
{
    $response = postJson(route('api.kiosk.devices.activate'), [
        'pin' => '123456',
        'device_name' => 'Kiosk Pinjam Uji',
    ])->assertSuccessful();

    return (string) $response->json('device_token');
}

/**
 * Helper: buat anggota yang siap meminjam (role + whatsapp terverifikasi).
 *
 * Email memuat NIM 9 digit karena `User::nim()` mengekstraknya dari bagian
 * setelah titik terakhir sebelum `@` (lihat `CampusEmail::extractIdentityNumber`).
 */
function borrowApiMember(array $attributes = []): User
{
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create(array_merge([
        'email' => 'anggota.'.random_int(100000000, 999999999).'@mhs.unimal.ac.id',
        'whatsapp' => '08'.str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus Bukit Indah',
    ], $attributes));
    $member->assignRole('member');

    return $member;
}

/**
 * Helper: buat buku beserta satu item dengan status tertentu.
 *
 * @return array{0: Book, 1: BookItem}
 */
function borrowApiBook(string $status = 'available', array $bookAttributes = []): array
{
    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Uji '.random_int(100000, 999999),
        'slug' => 'penerbit-uji-'.random_int(100000, 999999),
    ]);

    $book = Book::query()->create(array_merge([
        'title' => 'Buku Uji '.random_int(100000, 999999),
        'slug' => 'buku-uji-'.random_int(100000, 999999),
        'isbn' => '978602'.str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ], $bookAttributes));

    $item = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-'.random_int(100000, 999999),
        'status' => $status,
    ]);

    return [$book, $item];
}

// ---------------------------------------------------------------------------
// Peminjaman — validasi payload
// ---------------------------------------------------------------------------

it('rejects kiosk borrowing without a verification qr payload', function () {
    $token = borrowApiDeviceToken();

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => '230170001',
        'book_ids' => [1],
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors('verification_payload');
});

it('rejects expired verification qr payloads', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();
    [$book] = borrowApiBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    // Buang token dari cache agar payload dianggap kedaluwarsa.
    Cache::flush();

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors('verification_payload');
});

// ---------------------------------------------------------------------------
// Peminjaman — aturan bisnis
// ---------------------------------------------------------------------------

it('requires whatsapp and address before borrowing', function () {
    $member = borrowApiMember(['whatsapp' => '08123456789', 'address' => null]);
    [$book] = borrowApiBook();

    $service = app(KioskLoanService::class);

    expect(fn () => $service->borrow($member->nim(), [$book->id]))
        ->toThrow(ValidationException::class, 'Nomor WhatsApp dan alamat wajib diisi pada profil sebelum meminjam buku.');
});

it('cannot borrow a book marked as not borrowable', function () {
    $member = borrowApiMember();
    [$book] = borrowApiBook('available', ['is_borrowable' => false]);

    $service = app(KioskLoanService::class);

    expect(fn () => $service->borrow($member->nim(), [$book->id]))
        ->toThrow(ValidationException::class, "Buku {$book->title} ditandai tidak boleh dipinjam.");
});

// ---------------------------------------------------------------------------
// Peminjaman — alur sukses
// ---------------------------------------------------------------------------

it('borrows selected books using book ids and records the visit', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();
    [$book, $item] = borrowApiBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(201)
        ->assertJsonPath('loan.member.name', $member->name)
        ->assertJsonPath('loan.books_count', 1);

    expect($item->fresh()->status)->toBe('borrowed');

    $this->assertDatabaseHas('visit_logs', [
        'name' => $member->name,
        'identity_number' => $member->nim(),
        'purpose' => 'borrow_return',
        'notes' => 'Peminjaman mandiri di kiosk',
        'kiosk_device_id' => KioskDevice::query()->latest('id')->first()->getKey(),
    ]);
});

it('rejects borrowing when the typed identifier does not match the scanned member key', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();
    $otherMember = borrowApiMember();
    [$book, $item] = borrowApiBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $otherMember->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'member_identifier' => 'Identitas anggota tidak sesuai dengan member key yang discan.',
        ]);

    expect($item->fresh()->status)->toBe('available');
});

it('stores the loan even when receipt notification dispatch fails', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();
    [$book, $item] = borrowApiBook();

    $dispatcher = mock(Dispatcher::class);
    $dispatcher->shouldReceive('send')->andThrow(new RuntimeException('Daily limit exceeded by mail provider.'));
    app()->instance(Dispatcher::class, $dispatcher);

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(201);

    expect($item->fresh()->status)->toBe('borrowed');
});

// ---------------------------------------------------------------------------
// Pencarian buku
// ---------------------------------------------------------------------------

it('searches only borrowable and available books for borrowing', function () {
    $token = borrowApiDeviceToken();

    [$availableBook] = borrowApiBook('available', ['title' => 'Pemrograman Laravel Lanjut']);
    borrowApiBook('borrowed', ['title' => 'Pemrograman Laravel Habis']);
    borrowApiBook('available', ['title' => 'Referensi Internal Laravel', 'is_borrowable' => false]);

    getJson(route('api.kiosk.books.search', ['q' => 'Laravel', 'mode' => 'borrow']), [
        'X-Kiosk-Device-Token' => $token,
    ])
        ->assertSuccessful()
        ->assertJsonPath('books.0.id', $availableBook->id);

    $ids = collect(getJson(route('api.kiosk.books.search', ['q' => 'Laravel', 'mode' => 'borrow']), [
        'X-Kiosk-Device-Token' => $token,
    ])->json('books'))->pluck('id')->all();

    expect($ids)->toBe([$availableBook->id]);
});

// ---------------------------------------------------------------------------
// Pengembalian
// ---------------------------------------------------------------------------

it('returns selected books using book ids and records the visit', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();
    [$book, $item] = borrowApiBook('borrowed');

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    $loanItem = LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $item->id,
    ]);

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.return'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertSuccessful()
        ->assertJsonPath('returned_count', 1)
        ->assertJsonPath('member.name', $member->name);

    expect($loanItem->fresh()->returned_at)->not->toBeNull()
        ->and($item->fresh()->status)->toBe('available')
        ->and($loan->fresh()->status)->toBe(Loan::STATUS_RETURNED);

    $this->assertDatabaseHas('visit_logs', [
        'name' => $member->name,
        'identity_number' => $member->nim(),
        'purpose' => 'borrow_return',
        'notes' => 'Pengembalian buku di kiosk',
    ]);
});

it('rejects returns when the typed identifier does not match the scanned member key', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();
    $otherMember = borrowApiMember();
    [$book, $item] = borrowApiBook('borrowed');

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    $loanItem = LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $item->id,
    ]);

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.return'), [
        'member_identifier' => $otherMember->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'member_identifier' => 'Identitas anggota tidak sesuai dengan member key yang discan.',
        ]);

    expect($loanItem->fresh()->returned_at)->toBeNull()
        ->and($item->fresh()->status)->toBe('borrowed');
});

it('searches only active borrowed books for returns', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();
    $otherMember = borrowApiMember();

    [$borrowedBook, $borrowedItem] = borrowApiBook('borrowed', ['title' => 'Laravel Return Aktif']);
    [$returnedBook, $returnedItem] = borrowApiBook('available', ['title' => 'Laravel Sudah Kembali']);
    [$otherBook, $otherItem] = borrowApiBook('borrowed', ['title' => 'Laravel Member Lain']);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);
    LoanItem::query()->create(['loan_id' => $loan->id, 'book_item_id' => $borrowedItem->id]);

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

    $otherLoan = Loan::query()->create([
        'user_id' => $otherMember->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);
    LoanItem::query()->create(['loan_id' => $otherLoan->id, 'book_item_id' => $otherItem->id]);

    $response = getJson(route('api.kiosk.books.search', [
        'q' => 'Laravel',
        'mode' => 'return',
        'member_identifier' => $member->nim(),
    ]), ['X-Kiosk-Device-Token' => $token])->assertSuccessful();

    expect(collect($response->json('books'))->pluck('id')->all())->toBe([$borrowedBook->id]);
});

it('lists active borrowed books for returns without a search query', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();

    [$firstBook, $firstItem] = borrowApiBook('borrowed', ['title' => 'Buku Pinjaman Pertama']);
    [$secondBook, $secondItem] = borrowApiBook('borrowed', ['title' => 'Buku Pinjaman Kedua']);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);
    LoanItem::query()->create(['loan_id' => $loan->id, 'book_item_id' => $firstItem->id]);
    LoanItem::query()->create(['loan_id' => $loan->id, 'book_item_id' => $secondItem->id]);

    $response = getJson(route('api.kiosk.books.search', [
        'mode' => 'return',
        'member_identifier' => $member->nim(),
    ]), ['X-Kiosk-Device-Token' => $token])->assertSuccessful();

    expect(collect($response->json('books'))->pluck('id')->sort()->values()->all())
        ->toBe(collect([$firstBook->id, $secondBook->id])->sort()->values()->all());
});

it('borrows with a verification qr and returns using the member email', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember();
    [$book, $item] = borrowApiBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])->assertStatus(201);

    expect($item->fresh()->status)->toBe('borrowed');

    $returnVerification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.return'), [
        'member_identifier' => $member->email,
        'verification_payload' => $returnVerification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])->assertSuccessful();

    expect($item->fresh()->status)->toBe('available');
});

it('borrows with a verification qr and returns using the member phone number', function () {
    $token = borrowApiDeviceToken();
    $member = borrowApiMember(['whatsapp' => '08123456789']);
    [$book, $item] = borrowApiBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])->assertStatus(201);

    expect($item->fresh()->status)->toBe('borrowed');

    $returnVerification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.return'), [
        'member_identifier' => '08123456789',
        'verification_payload' => $returnVerification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])->assertSuccessful();

    expect($item->fresh()->status)->toBe('available');
});
