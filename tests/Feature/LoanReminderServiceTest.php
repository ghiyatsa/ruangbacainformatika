<?php

use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanReminderDatabaseNotification;
use App\Notifications\LoanReminderNotification;
use App\Services\LoanReminderService;
use Illuminate\Support\Facades\Notification;

it('sends reminders for all eligible active loans of a member', function () {
    Notification::fake();

    $member = User::factory()->create();
    $tomorrow = Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDay(),
        'reminder_sent_at' => null,
    ]);
    $overdue = Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(2),
        'reminder_sent_at' => null,
    ]);

    $sent = app(LoanReminderService::class)->remindAllActive($member);

    expect($sent)->toBe(2);

    Notification::assertSentTo($member, LoanReminderNotification::class, 2);
    Notification::assertSentTo($member, LoanReminderDatabaseNotification::class, 2);
    expect($tomorrow->fresh()->reminder_sent_at)->not->toBeNull();
    expect($overdue->fresh()->reminder_sent_at)->not->toBeNull();
});

it('skips loans that were already reminded today', function () {
    Notification::fake();

    $member = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(1),
        'reminder_sent_at' => now(),
    ]);

    $sent = app(LoanReminderService::class)->remindAllActive($member);

    expect($sent)->toBe(0);
    Notification::assertNothingSent();
});

it('skips returned loans and loans that are not due yet', function () {
    Notification::fake();

    $member = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'returned_at' => now(),
        'due_at' => now()->subDays(1),
        'reminder_sent_at' => null,
    ]);
    Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDays(5),
        'reminder_sent_at' => null,
    ]);

    $sent = app(LoanReminderService::class)->remindAllActive($member);

    expect($sent)->toBe(0);
    Notification::assertNothingSent();
});

it('reminds loans that are overdue for any length of time', function (int $daysLate) {
    Notification::fake();

    $member = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays($daysLate),
        'reminder_sent_at' => null,
    ]);

    $sent = app(LoanReminderService::class)->remindAllActive($member);

    expect($sent)->toBe(1);
    Notification::assertSentTo($member, LoanReminderNotification::class);
})->with([8, 15, 30, 90, 365]);

it('keeps the borrower list and the reminder window consistent', function (int $daysLate) {
    $member = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays($daysLate),
        'reminder_sent_at' => null,
    ]);

    // Aturan yang dipakai daftar peminjam "Hanya terlambat" di panel admin.
    $shownInList = User::query()
        ->whereHas('loans', fn ($q) => $q
            ->where('status', Loan::STATUS_BORROWED)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->where('due_at', '<=', LoanReminderService::reminderDueThreshold()))
        ->whereKey($member->id)
        ->exists();

    // Aturan yang dipakai pengirim pengingat.
    $eligibleForReminder = app(LoanReminderService::class)
        ->eligibleLoansQuery()
        ->whereBelongsTo($member)
        ->exists();

    expect($shownInList)->toBe($eligibleForReminder);
})->with([1, 7, 8, 30, 365]);

it('returns false when reminding an ineligible loan', function () {
    Notification::fake();

    $member = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDays(5),
        'reminder_sent_at' => null,
    ]);

    expect(app(LoanReminderService::class)->remind($loan))->toBeFalse();
    Notification::assertNothingSent();
});
