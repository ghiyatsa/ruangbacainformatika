<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\ReturnDraft;
use App\Models\User;
use App\Notifications\LoanReturnNotification;
use App\Services\KioskPinManager;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\from;
use function Pest\Laravel\instance;
use function Pest\Laravel\post;
use function Pest\Laravel\withoutMiddleware;

function ensureReturnMemberRole(): Role
{
    return Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
}

function fakeVerifiedReturnKioskPin(): void
{
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();

    instance(KioskPinManager::class, $mock);
}

it('consumes a qr return draft from kiosk and returns the selected books', function () {
    withoutMiddleware(PreventRequestForgery::class);
    Notification::fake();

    ensureReturnMemberRole();

    $member = User::factory()->create([
        'whatsapp' => '081234567895',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kiosk Return',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Kiosk Return',
        'slug' => 'penerbit-kiosk-return',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Kiosk Return',
        'slug' => 'buku-kiosk-return',
        'isbn' => '9786020012003',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-KIOSK-RETURN-001',
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(2),
    ]);

    $loanItem = LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $bookItem->id,
    ]);

    actingAs($member)->post(route('loans.history.qr'), [
        'loan_item_ids' => [$loanItem->id],
    ]);

    $payload = session('return_request_qr.payload');
    fakeVerifiedReturnKioskPin();

    post(route('kiosk.return-drafts.consume'), [
        'payload' => $payload,
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'return']))
        ->assertSessionHas('inertia.flash_data.toast.message', '1 buku berhasil dikembalikan.');

    $draft = ReturnDraft::query()->whereBelongsTo($member)->latest('id')->first();

    expect($draft)->toBeInstanceOf(ReturnDraft::class)
        ->and($draft?->status)->toBe(ReturnDraft::STATUS_CONSUMED)
        ->and($draft?->consumed_at)->not->toBeNull()
        ->and($loanItem->fresh()?->returned_at)->not->toBeNull()
        ->and($bookItem->fresh()?->status)->toBe('available')
        ->and($loan->fresh()?->status)->toBe(Loan::STATUS_RETURNED);

    Notification::assertSentTo(
        $member,
        LoanReturnNotification::class,
        fn (LoanReturnNotification $notification): bool => $notification->toArray($member)['book_titles'] === [$book->title]
    );
});

it('rejects expired qr return drafts from kiosk', function () {
    withoutMiddleware(PreventRequestForgery::class);

    ensureReturnMemberRole();

    $member = User::factory()->create([
        'whatsapp' => '081234567896',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Expired Return',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Expired Return',
        'slug' => 'penerbit-expired-return',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Expired Return',
        'slug' => 'buku-expired-return',
        'isbn' => '9786020012004',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-KIOSK-RETURN-002',
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(2),
    ]);

    $loanItem = LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $bookItem->id,
    ]);

    actingAs($member)->post(route('loans.history.qr'), [
        'loan_item_ids' => [$loanItem->id],
    ]);

    $payload = session('return_request_qr.payload');
    $draft = ReturnDraft::query()->whereBelongsTo($member)->latest('id')->first();
    $draft?->forceFill([
        'expires_at' => now()->subMinute(),
    ])->save();

    fakeVerifiedReturnKioskPin();

    from(route('kiosk.index', ['menu' => 'return']))
        ->post(route('kiosk.return-drafts.consume'), [
            'payload' => $payload,
        ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'return']))
        ->assertSessionHasErrors('payload');

    expect($draft?->fresh()?->status)->toBe(ReturnDraft::STATUS_EXPIRED)
        ->and($loanItem->fresh()?->returned_at)->toBeNull();
});
