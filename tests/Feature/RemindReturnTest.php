<?php

use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanReminderDatabaseNotification;
use App\Notifications\LoanReminderNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\artisan;

it('sends reminders for loans due tomorrow', function () {
    Notification::fake();

    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDay()->setHour(10)->setMinute(0),
        'reminder_sent_at' => null,
    ]);

    artisan('app:remind-return')
        ->expectsOutput('Mengirim reminder untuk 1 pinjaman...')
        ->expectsOutput('Reminder berhasil dikirim.')
        ->assertExitCode(0);

    Notification::assertSentTo(
        $user,
        LoanReminderNotification::class,
        fn ($notification) => $notification->toArray($user)['loan_id'] === $loan->id
    );
    Notification::assertSentTo(
        $user,
        LoanReminderDatabaseNotification::class,
        fn ($notification) => $notification->toArray($user)['loan_id'] === $loan->id
    );

    expect($loan->fresh()->reminder_sent_at)->not->toBeNull();
});

it('sends reminders for loans due today', function () {
    Notification::fake();

    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->setHour(10)->setMinute(0),
        'reminder_sent_at' => null,
    ]);

    artisan('app:remind-return')
        ->expectsOutput('Mengirim reminder untuk 1 pinjaman...')
        ->assertExitCode(0);

    Notification::assertSentTo($user, LoanReminderNotification::class);
    Notification::assertSentTo($user, LoanReminderDatabaseNotification::class);
});

it('sends reminders for overdue loans within the overdue cap', function () {
    Notification::fake();

    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(3),
        'reminder_sent_at' => null,
    ]);

    artisan('app:remind-return')
        ->expectsOutput('Mengirim reminder untuk 1 pinjaman...')
        ->assertExitCode(0);

    Notification::assertSentTo($user, LoanReminderNotification::class);
});

it('does not send reminders for loans beyond the overdue cap', function () {
    Notification::fake();

    $user = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(10),
        'reminder_sent_at' => null,
    ]);

    artisan('app:remind-return')
        ->expectsOutput('Tidak ada pinjaman yang perlu diingatkan hari ini.')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('does not send reminders for loans already reminded today', function () {
    Notification::fake();

    $user = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDay()->setHour(10)->setMinute(0),
        'reminder_sent_at' => now(),
    ]);

    artisan('app:remind-return')
        ->expectsOutput('Tidak ada pinjaman yang perlu diingatkan hari ini.')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('sends reminders again for loans reminded before today', function () {
    Notification::fake();

    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(2),
        'reminder_sent_at' => now()->subDay(),
    ]);

    artisan('app:remind-return')
        ->expectsOutput('Mengirim reminder untuk 1 pinjaman...')
        ->assertExitCode(0);

    Notification::assertSentTo($user, LoanReminderNotification::class);
});

it('does not send reminders for loans due later than tomorrow', function () {
    Notification::fake();

    $user = User::factory()->create();

    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDays(2),
    ]);

    artisan('app:remind-return')
        ->expectsOutput('Tidak ada pinjaman yang perlu diingatkan hari ini.')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('uses adaptive copy for the day before due date', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->addDay(),
    ]);

    $waNotification = new LoanReminderNotification($loan);
    $dbNotification = new LoanReminderDatabaseNotification($loan);

    expect($waNotification->toWhatsApp($user)->content)
        ->toContain('Peminjaman buku Anda berakhir besok.')
        ->and($dbNotification->toArray($user)['title'])->toBe('Batas pengembalian hampir tiba');
});

it('uses adaptive copy for loans due today', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now(),
    ]);

    $waNotification = new LoanReminderNotification($loan);
    $dbNotification = new LoanReminderDatabaseNotification($loan);

    expect($waNotification->toWhatsApp($user)->content)
        ->toContain('Peminjaman buku Anda berakhir hari ini.')
        ->and($dbNotification->toArray($user)['title'])->toBe('Batas pengembalian hari ini');
});

it('uses adaptive copy for overdue loans', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(3),
    ]);

    $waNotification = new LoanReminderNotification($loan);
    $dbNotification = new LoanReminderDatabaseNotification($loan);

    expect($waNotification->toWhatsApp($user)->content)
        ->toContain('sudah melewati jatuh tempo (telat 3 hari)')
        ->and($dbNotification->toArray($user)['title'])->toBe('Pengembalian sudah telat 3 hari');
});
