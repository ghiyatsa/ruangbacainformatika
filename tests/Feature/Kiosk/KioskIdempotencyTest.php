<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\KioskIdempotencyRecord;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\Setting;
use App\Models\User;
use App\Services\KioskBorrowVerificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

/**
 * Idempotensi transaksi kiosk.
 *
 * Skenario yang dilindungi: permintaan tulis berhasil di server tetapi
 * responsnya hilang (timeout/kabel putus), lalu klien mencoba lagi. Tanpa
 * penjagaan ini, percobaan kedua akan membuat transaksi baru (mis. pinjaman
 * ganda).
 */
beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
    Carbon::setTestNow('2026-06-07 03:00:00');

    // Rate limiter kiosk-submit membatasi percobaan ulang dalam test; matikan
    // agar pengujian idempotensi tidak tersandung throttle.
    RateLimiter::clear('kiosk-submit');

    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'pin_hash',
        'value' => Hash::make('123456'),
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function idempotencyDeviceToken(): string
{
    $response = postJson(route('api.kiosk.devices.activate'), [
        'pin' => '123456',
        'device_name' => 'Kiosk Idempotensi Uji',
    ])->assertSuccessful();

    return (string) $response->json('device_token');
}

function idempotencyMember(): User
{
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'email' => 'idem.'.random_int(100000000, 999999999).'@mhs.unimal.ac.id',
        'whatsapp' => '08'.str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus Bukit Indah',
    ]);
    $member->assignRole('member');

    return $member;
}

/**
 * @return array{0: Book, 1: BookItem}
 */
function idempotencyBook(string $status = 'available'): array
{
    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Idem '.random_int(100000, 999999),
        'slug' => 'penerbit-idem-'.random_int(100000, 999999),
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Idem '.random_int(100000, 999999),
        'slug' => 'buku-idem-'.random_int(100000, 999999),
        'isbn' => '978602'.str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $item = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'IDEM-'.random_int(100000, 999999),
        'status' => $status,
    ]);

    return [$book, $item];
}

it('replays the stored response when the same borrow key is retried', function () {
    $token = idempotencyDeviceToken();
    $member = idempotencyMember();
    [$book, $item] = idempotencyBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);
    $headers = ['X-Kiosk-Device-Token' => $token, 'Idempotency-Key' => 'borrow-retry-1'];

    $first = postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], $headers)->assertStatus(201);

    // Percobaan ulang memakai QR baru (QR sekali pakai), tetapi key sama.
    $retryVerification = app(KioskBorrowVerificationService::class)->generate($member);

    $second = postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $retryVerification['payload'],
        'book_ids' => [$book->id],
    ], $headers)->assertStatus(201);

    // Respons identik dan ditandai sebagai putar ulang.
    expect($second->json('loan.id'))->toBe($first->json('loan.id'))
        ->and($second->headers->get('Idempotency-Replayed'))->toBe('true');

    // Hanya satu pinjaman yang dibuat, dan hanya satu kunjungan tercatat.
    expect(Loan::query()->count())->toBe(1)
        ->and($item->fresh()->status)->toBe('borrowed');
});

it('creates only one loan when the same key is sent twice without a stored record', function () {
    $token = idempotencyDeviceToken();
    $member = idempotencyMember();
    [$book] = idempotencyBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);
    $headers = ['X-Kiosk-Device-Token' => $token, 'Idempotency-Key' => 'borrow-once'];

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], $headers)->assertStatus(201);

    $retryVerification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $retryVerification['payload'],
        'book_ids' => [$book->id],
    ], $headers)->assertStatus(201);

    expect(Loan::query()->count())->toBe(1);
});

it('rejects a key reused with a different payload', function () {
    $token = idempotencyDeviceToken();
    $member = idempotencyMember();
    [$firstBook] = idempotencyBook();
    [$secondBook] = idempotencyBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);
    $headers = ['X-Kiosk-Device-Token' => $token, 'Idempotency-Key' => 'borrow-conflict'];

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$firstBook->id],
    ], $headers)->assertStatus(201);

    $retryVerification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $retryVerification['payload'],
        'book_ids' => [$secondBook->id],
    ], $headers)
        ->assertStatus(409)
        ->assertJsonPath('message', 'Idempotency-Key sudah dipakai untuk permintaan yang berbeda.');
});

it('does not store failed responses so they can be corrected and retried', function () {
    $token = idempotencyDeviceToken();
    $member = idempotencyMember();
    [$book] = idempotencyBook();

    $headers = ['X-Kiosk-Device-Token' => $token, 'Idempotency-Key' => 'borrow-fail'];

    // Payload QR tidak valid -> 422, tidak boleh disimpan.
    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => 'payload-palsu',
        'book_ids' => [$book->id],
    ], $headers)->assertStatus(422);

    expect(KioskIdempotencyRecord::query()->where('key', 'borrow-fail')->exists())->toBeFalse();

    // Percobaan ulang dengan payload benar harus berhasil.
    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], $headers)->assertStatus(201);
});

it('keeps requests without an idempotency key working as before', function () {
    $token = idempotencyDeviceToken();
    $member = idempotencyMember();
    [$book] = idempotencyBook();

    $verification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.borrow'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], ['X-Kiosk-Device-Token' => $token])->assertStatus(201);

    expect(KioskIdempotencyRecord::query()->count())->toBe(0);
});

it('replays the stored response when the same return key is retried', function () {
    $token = idempotencyDeviceToken();
    $member = idempotencyMember();
    [$book, $item] = idempotencyBook('borrowed');

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);
    LoanItem::query()->create(['loan_id' => $loan->id, 'book_item_id' => $item->id]);

    $verification = app(KioskBorrowVerificationService::class)->generate($member);
    $headers = ['X-Kiosk-Device-Token' => $token, 'Idempotency-Key' => 'return-retry-1'];

    postJson(route('api.kiosk.loans.return'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $verification['payload'],
        'book_ids' => [$book->id],
    ], $headers)->assertSuccessful();

    $retryVerification = app(KioskBorrowVerificationService::class)->generate($member);

    postJson(route('api.kiosk.loans.return'), [
        'member_identifier' => $member->nim(),
        'verification_payload' => $retryVerification['payload'],
        'book_ids' => [$book->id],
    ], $headers)
        ->assertSuccessful()
        ->assertJsonPath('returned_count', 1);

    expect($loan->fresh()->status)->toBe(Loan::STATUS_RETURNED);
});
