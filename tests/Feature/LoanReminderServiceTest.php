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

it('skips returned or out-of-window loans', function () {
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
        'due_at' => now()->subDays(10),
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
