<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanDraft;
use App\Models\LoanDraftItem;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\LoanReceiptNotification;
use App\Services\KioskPinManager;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\from;
use function Pest\Laravel\instance;
use function Pest\Laravel\post;
use function Pest\Laravel\withoutMiddleware;

function ensureMemberRole(): Role
{
    return Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
}

function makePublisher(string $name, string $slug): Publisher
{
    return Publisher::query()->create([
        'name' => $name,
        'slug' => $slug,
    ]);
}

function makeBorrowableBook(Publisher $publisher, string $title, string $slug, string $isbn): Book
{
    return Book::query()->create([
        'title' => $title,
        'slug' => $slug,
        'isbn' => $isbn,
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);
}

function makeBookItem(Book $book, string $internalCode, string $status): BookItem
{
    return BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => $internalCode,
        'status' => $status,
    ]);
}

function fakeVerifiedKioskPin(): void
{
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();

    instance(KioskPinManager::class, $mock);
}

it('consumes a qr loan draft from kiosk and creates the final loan', function () {
    withoutMiddleware(PreventRequestForgery::class);

    ensureMemberRole();
    Notification::fake();

    $member = User::factory()->create();
    /** @var User $member */
    $member->assignRole('member');

    $publisher = makePublisher('Penerbit Consume', 'penerbit-consume');
    $book = makeBorrowableBook($publisher, 'Buku Consume QR', 'buku-consume-qr', '9786020000202');
    $bookItem = makeBookItem($book, 'ITEM-CONSUME-001', 'available');

    actingAs($member)->post(route('loans.request.books.store'), [
        'book_id' => $book->id,
    ]);

    actingAs($member)->post(route('loans.request.qr'));

    $payload = session('loan_request_qr.payload');
    /** @var string $payload */
    fakeVerifiedKioskPin();

    post(route('kiosk.loan-drafts.consume'), [
        'payload' => $payload,
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSessionHas('inertia.flash_data.toast.message', '1 buku berhasil dipinjam.');

    $draft = LoanDraft::query()->whereBelongsTo($member)->latest('id')->first();

    if (! $draft instanceof LoanDraft) {
        test()->fail('Loan draft was not created.');
    }

    expect($draft->status)->toBe(LoanDraft::STATUS_CONSUMED)
        ->and($draft->consumed_at)->not->toBeNull()
        ->and($bookItem->fresh()->status)->toBe('borrowed');

    assertDatabaseCount('loans', 1);

    Notification::assertSentTo($member, LoanReceiptNotification::class);
});

it('rejects expired qr loan drafts from kiosk', function () {
    withoutMiddleware(PreventRequestForgery::class);

    ensureMemberRole();

    $member = User::factory()->create();
    /** @var User $member */
    $member->assignRole('member');

    $publisher = makePublisher('Penerbit Expired', 'penerbit-expired');
    $book = makeBorrowableBook($publisher, 'Buku Expired QR', 'buku-expired-qr', '9786020000203');
    makeBookItem($book, 'ITEM-EXPIRED-001', 'available');

    actingAs($member)->post(route('loans.request.books.store'), [
        'book_id' => $book->id,
    ]);

    actingAs($member)->post(route('loans.request.qr'));

    $payload = session('loan_request_qr.payload');
    /** @var string $payload */
    $draft = LoanDraft::query()
        ->whereBelongsTo($member)
        ->latest('id')
        ->first();

    if (! $draft instanceof LoanDraft) {
        test()->fail('Loan draft was not created.');
    }

    $draft->forceFill([
        'expires_at' => now()->subMinute(),
    ])->save();

    fakeVerifiedKioskPin();

    from(route('kiosk.index', ['menu' => 'borrow']))
        ->post(route('kiosk.loan-drafts.consume'), [
            'payload' => $payload,
        ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSessionHasErrors('payload');

    $draft = LoanDraft::query()->whereBelongsTo($member)->latest('id')->first();

    if (! $draft instanceof LoanDraft) {
        test()->fail('Loan draft should still exist after being expired.');
    }

    expect($draft->status)->toBe(LoanDraft::STATUS_EXPIRED);

    assertDatabaseCount('loans', 0);
});

it('rejects qr loan draft consumption when member already reached the loan limit', function () {
    withoutMiddleware(PreventRequestForgery::class);

    ensureMemberRole();

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'loan_max_books'],
        ['value' => '3'],
    );

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'address' => 'Jl. Banda Aceh No. 10',
    ]);
    /** @var User $member */
    $member->assignRole('member');

    $publisher = makePublisher('Penerbit Batas Limit', 'penerbit-batas-limit');

    $activeLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    foreach ([1, 2, 3] as $index) {
        $book = makeBorrowableBook($publisher, "Buku Aktif {$index}", "buku-aktif-{$index}", '97860200003'.$index);
        $bookItem = makeBookItem($book, "ITEM-ACTIVE-{$index}", 'borrowed');

        LoanItem::query()->create([
            'loan_id' => $activeLoan->id,
            'book_item_id' => $bookItem->id,
        ]);
    }

    $draftBook = makeBorrowableBook($publisher, 'Buku Draft QR', 'buku-draft-qr', '9786020000399');
    makeBookItem($draftBook, 'ITEM-DRAFT-QR', 'available');

    $payload = 'RB-LOAN-LIMIT-TEST-TOKEN';
    $draft = LoanDraft::query()->create([
        'user_id' => $member->id,
        'status' => LoanDraft::STATUS_PENDING,
        'token_hash' => hash('sha256', $payload),
        'expires_at' => now()->addMinutes(10),
    ]);

    LoanDraftItem::query()->create([
        'loan_draft_id' => $draft->id,
        'book_id' => $draftBook->id,
    ]);

    fakeVerifiedKioskPin();

    from(route('kiosk.index', ['menu' => 'borrow']))
        ->post(route('kiosk.loan-drafts.consume'), [
            'payload' => $payload,
        ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSessionHasErrors('book_ids');

    $draft->refresh();

    expect($draft->status)->toBe(LoanDraft::STATUS_PENDING);

    assertDatabaseCount('loans', 1);
});
