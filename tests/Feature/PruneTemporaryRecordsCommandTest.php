<?php

use App\Models\Book;
use App\Models\KioskDevice;
use App\Models\LoanDraft;
use App\Models\LoanDraftItem;
use App\Models\LoanItem;
use App\Models\ReturnDraft;
use App\Models\ReturnDraftItem;
use App\Models\User;
use Illuminate\Support\Carbon;

use function Pest\Laravel\artisan;

it('prunes stale temporary drafts and kiosk devices', function () {
    Carbon::setTestNow('2026-05-29 09:00:00');

    $user = User::factory()->create();
    $book = Book::factory()->create();
    $loanItem = LoanItem::factory()->create();

    $staleConsumedLoanDraft = LoanDraft::factory()->create([
        'user_id' => $user->id,
        'status' => LoanDraft::STATUS_CONSUMED,
        'consumed_at' => now()->subDays(10),
    ]);
    $staleExpiredLoanDraft = LoanDraft::factory()->create([
        'user_id' => $user->id,
        'status' => LoanDraft::STATUS_EXPIRED,
        'expires_at' => now()->subDays(8),
    ]);
    $stalePendingExpiredLoanDraft = LoanDraft::factory()->create([
        'user_id' => $user->id,
        'status' => LoanDraft::STATUS_PENDING,
        'token_hash' => hash('sha256', 'stale-loan-pending'),
        'expires_at' => now()->subDays(9),
    ]);

    LoanDraftItem::factory()->create([
        'loan_draft_id' => $staleConsumedLoanDraft->id,
        'book_id' => $book->id,
    ]);

    $recentLoanDraft = LoanDraft::factory()->create([
        'user_id' => $user->id,
        'status' => LoanDraft::STATUS_CONSUMED,
        'consumed_at' => now()->subDays(2),
    ]);
    $activeLoanDraft = LoanDraft::factory()->readyForScan()->create([
        'user_id' => $user->id,
    ]);

    $staleConsumedReturnDraft = ReturnDraft::factory()->create([
        'user_id' => $user->id,
        'status' => ReturnDraft::STATUS_CONSUMED,
        'consumed_at' => now()->subDays(10),
    ]);
    $staleExpiredReturnDraft = ReturnDraft::factory()->create([
        'user_id' => $user->id,
        'status' => ReturnDraft::STATUS_EXPIRED,
        'expires_at' => now()->subDays(8),
    ]);
    $stalePendingExpiredReturnDraft = ReturnDraft::factory()->create([
        'user_id' => $user->id,
        'status' => ReturnDraft::STATUS_PENDING,
        'token_hash' => hash('sha256', 'stale-return-pending'),
        'expires_at' => now()->subDays(9),
    ]);

    ReturnDraftItem::factory()->create([
        'return_draft_id' => $staleConsumedReturnDraft->id,
        'loan_item_id' => $loanItem->id,
    ]);

    $recentReturnDraft = ReturnDraft::factory()->create([
        'user_id' => $user->id,
        'status' => ReturnDraft::STATUS_CONSUMED,
        'consumed_at' => now()->subDays(2),
    ]);
    $activeReturnDraft = ReturnDraft::factory()->create([
        'user_id' => $user->id,
        'status' => ReturnDraft::STATUS_PENDING,
        'token_hash' => hash('sha256', 'active-return'),
        'expires_at' => now()->addMinutes(10),
    ]);

    $staleDevice = KioskDevice::query()->create([
        'session_id' => 'stale-session',
        'device_token' => str_repeat('a', 64),
        'ip_address' => '192.168.1.10',
        'last_active_at' => now()->subDays(40),
    ]);
    $activeDevice = KioskDevice::query()->create([
        'session_id' => 'active-session',
        'device_token' => str_repeat('b', 64),
        'ip_address' => '192.168.1.11',
        'last_active_at' => now()->subDays(3),
    ]);

    artisan('app:prune-temporary-records --draft-days=7 --device-days=30')
        ->expectsOutput('Pruned 3 loan drafts.')
        ->expectsOutput('Pruned 3 return drafts.')
        ->expectsOutput('Pruned 1 kiosk devices.')
        ->assertSuccessful();

    expect(LoanDraft::query()->whereKey($staleConsumedLoanDraft->id)->exists())->toBeFalse()
        ->and(LoanDraft::query()->whereKey($staleExpiredLoanDraft->id)->exists())->toBeFalse()
        ->and(LoanDraft::query()->whereKey($stalePendingExpiredLoanDraft->id)->exists())->toBeFalse()
        ->and(LoanDraft::query()->whereKey($recentLoanDraft->id)->exists())->toBeTrue()
        ->and(LoanDraft::query()->whereKey($activeLoanDraft->id)->exists())->toBeTrue()
        ->and(LoanDraftItem::query()->where('loan_draft_id', $staleConsumedLoanDraft->id)->exists())->toBeFalse()
        ->and(ReturnDraft::query()->whereKey($staleConsumedReturnDraft->id)->exists())->toBeFalse()
        ->and(ReturnDraft::query()->whereKey($staleExpiredReturnDraft->id)->exists())->toBeFalse()
        ->and(ReturnDraft::query()->whereKey($stalePendingExpiredReturnDraft->id)->exists())->toBeFalse()
        ->and(ReturnDraft::query()->whereKey($recentReturnDraft->id)->exists())->toBeTrue()
        ->and(ReturnDraft::query()->whereKey($activeReturnDraft->id)->exists())->toBeTrue()
        ->and(ReturnDraftItem::query()->where('return_draft_id', $staleConsumedReturnDraft->id)->exists())->toBeFalse()
        ->and(KioskDevice::query()->whereKey($staleDevice->id)->exists())->toBeFalse()
        ->and(KioskDevice::query()->whereKey($activeDevice->id)->exists())->toBeTrue();
});
