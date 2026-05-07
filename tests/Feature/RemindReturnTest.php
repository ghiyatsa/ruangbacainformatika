<?php

use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanReminderNotification;
use Illuminate\Support\Facades\Notification;

it('sends reminders for loans due tomorrow', function () {
    Notification::fake();

    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDay()->setHour(10)->setMinute(0),
        'reminder_sent_at' => null,
    ]);

    $this->artisan('app:remind-return')
        ->expectsOutput('Sending reminders for 1 loans...')
        ->expectsOutput('Reminders sent successfully!')
        ->assertExitCode(0);

    Notification::assertSentTo(
        $user,
        LoanReminderNotification::class,
        fn ($notification) => $notification->toArray($user)['loan_id'] === $loan->id
    );

    expect($loan->fresh()->reminder_sent_at)->not->toBeNull();
});

it('does not send reminders for loans already reminded', function () {
    Notification::fake();

    $user = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDay()->setHour(10)->setMinute(0),
        'reminder_sent_at' => now(),
    ]);

    $this->artisan('app:remind-return')
        ->expectsOutput('No books are due tomorrow.')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('does not send reminders for loans not due tomorrow', function () {
    Notification::fake();

    $user = User::factory()->create();

    // Due today
    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->setHour(10),
    ]);

    // Due in 2 days
    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDays(2),
    ]);

    $this->artisan('app:remind-return')
        ->expectsOutput('No books are due tomorrow.')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});
