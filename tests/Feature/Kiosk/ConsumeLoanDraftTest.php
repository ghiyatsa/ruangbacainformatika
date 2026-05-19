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

use function Pest\Laravel\instance;

it('consumes a qr loan draft from kiosk and creates the final loan', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    Notification::fake();

    $member = User::factory()->create();
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Consume',
        'slug' => 'penerbit-consume',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Consume QR',
        'slug' => 'buku-consume-qr',
        'isbn' => '9786020000202',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-CONSUME-001',
        'status' => 'available',
    ]);

    $this->actingAs($member)->post(route('loans.request.books.store'), [
        'book_id' => $book->id,
    ]);

    $this->actingAs($member)->post(route('loans.request.qr'));

    $payload = session('loan_request_qr.payload');

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $response = $this->post(route('kiosk.loan-drafts.consume'), [
        'payload' => $payload,
    ]);

    $response
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSessionHas('inertia.flash_data.toast.message', '1 buku berhasil dipinjam.');

    $draft = LoanDraft::query()->whereBelongsTo($member)->latest('id')->first();

    expect($draft)->not->toBeNull()
        ->and($draft->status)->toBe(LoanDraft::STATUS_CONSUMED)
        ->and($draft->consumed_at)->not->toBeNull()
        ->and($bookItem->fresh()->status)->toBe('borrowed');

    expect(Loan::query()->whereBelongsTo($member)->count())->toBe(1);

    Notification::assertSentTo($member, LoanReceiptNotification::class);
});

it('rejects expired qr loan drafts from kiosk', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create();
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Expired',
        'slug' => 'penerbit-expired',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Expired QR',
        'slug' => 'buku-expired-qr',
        'isbn' => '9786020000203',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-EXPIRED-001',
        'status' => 'available',
    ]);

    $this->actingAs($member)->post(route('loans.request.books.store'), [
        'book_id' => $book->id,
    ]);

    $this->actingAs($member)->post(route('loans.request.qr'));

    $payload = session('loan_request_qr.payload');

    LoanDraft::query()
        ->whereBelongsTo($member)
        ->latest('id')
        ->first()
        ?->forceFill([
            'expires_at' => now()->subMinute(),
        ])->save();

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $response = $this->from(route('kiosk.index', ['menu' => 'borrow']))
        ->post(route('kiosk.loan-drafts.consume'), [
            'payload' => $payload,
        ]);

    $response
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSessionHasErrors('payload');

    $draft = LoanDraft::query()->whereBelongsTo($member)->latest('id')->first();

    expect($draft)->not->toBeNull()
        ->and($draft->status)->toBe(LoanDraft::STATUS_EXPIRED)
        ->and(Loan::query()->count())->toBe(0);
});

it('rejects qr loan draft consumption when member already reached the loan limit', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'loan_max_books'],
        ['value' => '3'],
    );

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
        'address' => 'Jl. Banda Aceh No. 10',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Batas Limit',
        'slug' => 'penerbit-batas-limit',
    ]);

    $activeLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    foreach ([1, 2, 3] as $index) {
        $book = Book::query()->create([
            'title' => "Buku Aktif {$index}",
            'slug' => "buku-aktif-{$index}",
            'isbn' => '97860200003'.$index,
            'publisher_id' => $publisher->id,
            'is_published' => true,
            'is_borrowable' => true,
        ]);

        $bookItem = BookItem::query()->create([
            'book_id' => $book->id,
            'internal_code' => "ITEM-ACTIVE-{$index}",
            'status' => 'borrowed',
        ]);

        LoanItem::query()->create([
            'loan_id' => $activeLoan->id,
            'book_item_id' => $bookItem->id,
        ]);
    }

    $draftBook = Book::query()->create([
        'title' => 'Buku Draft QR',
        'slug' => 'buku-draft-qr',
        'isbn' => '9786020000399',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $draftBook->id,
        'internal_code' => 'ITEM-DRAFT-QR',
        'status' => 'available',
    ]);

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

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $response = $this->from(route('kiosk.index', ['menu' => 'borrow']))
        ->post(route('kiosk.loan-drafts.consume'), [
            'payload' => $payload,
        ]);

    $response
        ->assertRedirect(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSessionHasErrors('book_ids');

    expect($draft->fresh()?->status)->toBe(LoanDraft::STATUS_PENDING)
        ->and(Loan::query()->count())->toBe(1);
});
