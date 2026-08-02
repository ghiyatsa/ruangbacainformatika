<?php

use App\Filament\Resources\Loans\Pages\ListLoans;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\LoanReminderDatabaseNotification;
use App\Notifications\LoanReminderNotification;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function loanActionAdmin(): User
{
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('sends a return reminder from the loans table action', function () {
    Notification::fake();

    $member = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(1),
        'reminder_sent_at' => null,
    ]);
    $member->loadCount([
        'loans as active_loans_count' => fn ($q) => $q->where('status', Loan::STATUS_BORROWED),
    ]);

    actingAs(loanActionAdmin());

    Livewire::test(ListLoans::class)
        ->callAction(TestAction::make('remindReturn')->table($member));

    Notification::assertSentTo($member, LoanReminderNotification::class);
    Notification::assertSentTo($member, LoanReminderDatabaseNotification::class);
    expect($loan->fresh()->reminder_sent_at)->not->toBeNull();
});

it('sends return reminders for selected members via the bulk action', function () {
    Notification::fake();

    $member = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(1),
        'reminder_sent_at' => null,
    ]);

    actingAs(loanActionAdmin());

    Livewire::test(ListLoans::class)
        ->callTableBulkAction('remindReturnSelected', [$member->id]);

    Notification::assertSentTo($member, LoanReminderNotification::class);
});
