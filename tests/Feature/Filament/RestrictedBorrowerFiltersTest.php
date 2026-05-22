<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\Setting;
use App\Models\User;
use Spatie\Permission\Models\Role;

function enableRestrictedBorrowerRule(): void
{
    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_suspension_enabled'],
        ['value' => '1'],
    );

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_suspend_after_days'],
        ['value' => '1'],
    );

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_cooldown_days'],
        ['value' => '3'],
    );
}

function makeBorrowableBookForRestrictedFilter(Publisher $publisher, string $suffix, string $isbn): Book
{
    return Book::query()->create([
        'title' => "Buku {$suffix}",
        'slug' => "buku-{$suffix}",
        'isbn' => $isbn,
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);
}

it('returns only restricted users from the borrowing restricted scope', function () {
    enableRestrictedBorrowerRule();

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $restrictedMember = User::factory()->create();
    $restrictedMember->assignRole('member');

    $normalMember = User::factory()->create();
    $normalMember->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Filter Scope',
        'slug' => 'penerbit-filter-scope',
    ]);

    $restrictedBook = makeBorrowableBookForRestrictedFilter($publisher, 'Filter Scope', '9786020000411');
    $restrictedItem = BookItem::query()->create([
        'book_id' => $restrictedBook->id,
        'internal_code' => 'ITEM-FILTER-SCOPE',
        'status' => 'borrowed',
    ]);

    $restrictedLoan = Loan::query()->create([
        'user_id' => $restrictedMember->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDays(5),
        'due_at' => now()->subDays(2),
    ]);

    LoanItem::query()->create([
        'loan_id' => $restrictedLoan->id,
        'book_item_id' => $restrictedItem->id,
    ]);

    $normalBook = makeBorrowableBookForRestrictedFilter($publisher, 'Filter Normal', '9786020000412');
    $normalItem = BookItem::query()->create([
        'book_id' => $normalBook->id,
        'internal_code' => 'ITEM-FILTER-NORMAL',
        'status' => 'borrowed',
    ]);

    $normalLoan = Loan::query()->create([
        'user_id' => $normalMember->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(2),
    ]);

    LoanItem::query()->create([
        'loan_id' => $normalLoan->id,
        'book_item_id' => $normalItem->id,
    ]);

    $restrictedIds = User::query()
        ->borrowingRestricted()
        ->pluck('id')
        ->all();

    expect($restrictedIds)->toContain($restrictedMember->id)
        ->not->toContain($normalMember->id);
});

it('returns users in cooldown after a late return from the borrowing restricted scope', function () {
    enableRestrictedBorrowerRule();

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $restrictedMember = User::factory()->create();
    $restrictedMember->assignRole('member');

    $normalMember = User::factory()->create();
    $normalMember->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Filter Cooldown',
        'slug' => 'penerbit-filter-cooldown',
    ]);

    $restrictedBook = makeBorrowableBookForRestrictedFilter($publisher, 'Cooldown Restricted', '9786020000413');
    $restrictedItem = BookItem::query()->create([
        'book_id' => $restrictedBook->id,
        'internal_code' => 'ITEM-FILTER-COOLDOWN',
        'status' => 'available',
    ]);

    $restrictedLoan = Loan::query()->create([
        'user_id' => $restrictedMember->id,
        'status' => Loan::STATUS_RETURNED,
        'borrowed_at' => now()->subDays(8),
        'due_at' => now()->subDays(4),
        'returned_at' => now()->subDay(),
    ]);

    LoanItem::query()->create([
        'loan_id' => $restrictedLoan->id,
        'book_item_id' => $restrictedItem->id,
        'returned_at' => now()->subDay(),
    ]);

    $normalBook = makeBorrowableBookForRestrictedFilter($publisher, 'Cooldown Normal', '9786020000414');
    $normalItem = BookItem::query()->create([
        'book_id' => $normalBook->id,
        'internal_code' => 'ITEM-FILTER-COOLDOWN-NORMAL',
        'status' => 'available',
    ]);

    $normalLoan = Loan::query()->create([
        'user_id' => $normalMember->id,
        'status' => Loan::STATUS_RETURNED,
        'borrowed_at' => now()->subDays(6),
        'due_at' => now()->subDays(2),
        'returned_at' => now()->subDays(2),
    ]);

    LoanItem::query()->create([
        'loan_id' => $normalLoan->id,
        'book_item_id' => $normalItem->id,
        'returned_at' => now()->subDays(2),
    ]);

    $restrictedIds = User::query()
        ->borrowingRestricted()
        ->pluck('id')
        ->all();

    expect($restrictedIds)->toContain($restrictedMember->id)
        ->not->toContain($normalMember->id);
});
