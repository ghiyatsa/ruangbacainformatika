<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\User;
use App\Notifications\LoanReceiptDatabaseNotification;
use App\Notifications\LoanReminderDatabaseNotification;
use Filament\Notifications\Notification as FilamentNotification;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

it('shares unread notification count for authenticated users', function () {
    $user = createNotificationMember();
    $loan = createLoanWithBookFor($user);

    $user->notifyNow(new LoanReceiptDatabaseNotification($loan));
    FilamentNotification::make()
        ->title('Import completed')
        ->body('Ada pembaruan pada akun Anda.')
        ->sendToDatabase($user);

    actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->where('notifications.unreadCount', 1),
        );
});

it('does not share unread notification count for non members', function () {
    $user = User::factory()->create();
    $loan = createLoanWithBookFor($user);

    $user->notifyNow(new LoanReceiptDatabaseNotification($loan));

    actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('auth.canViewNotifications', false)
                ->where('notifications.unreadCount', 0),
        );
});

it('returns the latest notifications for the authenticated user', function () {
    $user = createNotificationMember();
    $loan = createLoanWithBookFor($user);

    $user->notifyNow(new LoanReceiptDatabaseNotification($loan));
    $user->notifyNow(new LoanReminderDatabaseNotification($loan));

    actingAs($user)
        ->getJson(route('notifications.index'))
        ->assertOk()
        ->assertJsonPath('unreadCount', 2)
        ->assertJsonCount(2, 'notifications');

    $titles = collect(
        actingAs($user)
            ->getJson(route('notifications.index'))
            ->json('notifications'),
    )->pluck('title');

    expect($titles)->toContain('Batas pengembalian hampir tiba')
        ->and($titles)->toContain('Peminjaman berhasil diproses');
});

it('excludes filament notifications from the public notification center', function () {
    $user = createNotificationMember();
    $loan = createLoanWithBookFor($user);

    $user->notifyNow(new LoanReceiptDatabaseNotification($loan));
    FilamentNotification::make()
        ->title('Import completed')
        ->body('Ada pembaruan pada akun Anda.')
        ->sendToDatabase($user);

    actingAs($user)
        ->getJson(route('notifications.index'))
        ->assertOk()
        ->assertJsonPath('unreadCount', 1)
        ->assertJsonCount(1, 'notifications')
        ->assertJsonMissing([
            'title' => 'Import completed',
        ]);
});

it('blocks the public notification center for non members', function () {
    $user = User::factory()->create();
    $loan = createLoanWithBookFor($user);

    $user->notifyNow(new LoanReceiptDatabaseNotification($loan));

    actingAs($user)
        ->getJson(route('notifications.index'))
        ->assertForbidden();
});

it('marks a notification as read', function () {
    $user = createNotificationMember();
    $loan = createLoanWithBookFor($user);

    $user->notifyNow(new LoanReceiptDatabaseNotification($loan));

    $notificationId = $user->notifications()->firstOrFail()->getKey();

    actingAs($user)
        ->postJson(route('notifications.read', $notificationId))
        ->assertOk()
        ->assertJsonPath('unreadCount', 0);

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

it('marks all notifications as read', function () {
    $user = createNotificationMember();
    $loan = createLoanWithBookFor($user);

    $user->notifyNow(new LoanReceiptDatabaseNotification($loan));
    $user->notifyNow(new LoanReminderDatabaseNotification($loan));
    FilamentNotification::make()
        ->title('Rekonsiliasi similarity selesai')
        ->body('Ada pembaruan pada akun Anda.')
        ->sendToDatabase($user);

    actingAs($user)
        ->postJson(route('notifications.read-all'))
        ->assertOk()
        ->assertJsonPath('unreadCount', 0);

    expect($user->fresh()->unreadNotifications()->count())->toBe(1);
});

function createNotificationMember(): User
{
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    return User::factory()->create([
        'whatsapp_verified_at' => now(),
    ]);
}

function createLoanWithBookFor(User $user): Loan
{
    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Notifikasi',
        'slug' => fake()->slug(),
    ]);

    $book = Book::query()->create([
        'title' => 'Laravel Notification Center',
        'slug' => fake()->unique()->slug(),
        'isbn' => '9786020000301',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => fake()->unique()->bothify('ITEM-###'),
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);

    LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $bookItem->id,
    ]);

    return $loan->load('items.bookItem.book');
}
