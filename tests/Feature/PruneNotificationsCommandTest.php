<?php

use App\Models\User;
use App\Notifications\LoanReminderDatabaseNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;

it('prunes notifications older than the retention period', function () {
    $user = User::factory()->create();

    $old = $user->notifications()->create([
        'id' => Str::uuid(),
        'type' => LoanReminderDatabaseNotification::class,
        'data' => ['title' => 'lama'],
        'created_at' => now()->subDays(10),
    ]);

    $recent = $user->notifications()->create([
        'id' => Str::uuid(),
        'type' => LoanReminderDatabaseNotification::class,
        'data' => ['title' => 'baru'],
        'created_at' => now()->subDay(),
    ]);

    artisan('app:prune-notifications')
        ->expectsOutput('Berhasil menghapus 1 notifikasi yang sudah lama.')
        ->assertExitCode(0);

    expect(DatabaseNotification::find($old->id))->toBeNull();
    expect(DatabaseNotification::find($recent->id))->not->toBeNull();
});

it('respects the retention-days option', function () {
    $user = User::factory()->create();

    $old = $user->notifications()->create([
        'id' => Str::uuid(),
        'type' => LoanReminderDatabaseNotification::class,
        'data' => ['title' => 'lama'],
        'created_at' => now()->subDays(8),
    ]);

    $recent = $user->notifications()->create([
        'id' => Str::uuid(),
        'type' => LoanReminderDatabaseNotification::class,
        'data' => ['title' => 'baru'],
        'created_at' => now()->subDays(6),
    ]);

    artisan('app:prune-notifications --retention-days=7')
        ->expectsOutput('Berhasil menghapus 1 notifikasi yang sudah lama.')
        ->assertExitCode(0);

    expect(DatabaseNotification::find($old->id))->toBeNull();
    expect(DatabaseNotification::find($recent->id))->not->toBeNull();
});
