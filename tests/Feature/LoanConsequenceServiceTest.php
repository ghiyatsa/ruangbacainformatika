<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\Setting;
use App\Models\User;
use App\Support\LoanConsequenceService;
use Spatie\Permission\Models\Role;

it('returns a restricted access summary for members with overdue active loans', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_suspension_enabled'],
        ['value' => '1'],
    );

    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_suspend_after_days'],
        ['value' => '1'],
    );

    $member = User::factory()->create();
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Status Aktif',
        'slug' => 'penerbit-status-aktif',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Status Aktif',
        'slug' => 'buku-status-aktif',
        'isbn' => '9786020000301',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $item = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-STATUS-AKTIF',
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDays(5),
        'due_at' => now()->subDays(2),
    ]);

    LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $item->id,
    ]);

    $summary = app(LoanConsequenceService::class)->borrowingAccessSummary($member);

    expect($summary['label'])->toBe('Dibatasi')
        ->and($summary['color'])->toBe('danger')
        ->and($summary['detail'])->toContain('terlambat 2 hari');
});

it('returns a normal access summary for members without active restrictions', function () {
    $member = User::factory()->create();

    $summary = app(LoanConsequenceService::class)->borrowingAccessSummary($member);

    expect($summary)->toBe([
        'label' => 'Normal',
        'color' => 'success',
        'detail' => 'Anggota dapat melakukan peminjaman baru.',
    ]);
});
