<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

it('shows loan history stats and rows per borrowed book', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '081234567890',
        'address' => 'Jl. Kampus',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Riwayat',
        'slug' => 'penerbit-riwayat',
    ]);

    $firstBook = Book::query()->create([
        'title' => 'Buku Aktif Pertama',
        'slug' => 'buku-aktif-pertama',
        'isbn' => '9786020010001',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $secondBook = Book::query()->create([
        'title' => 'Buku Aktif Kedua',
        'slug' => 'buku-aktif-kedua',
        'isbn' => '9786020010002',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $returnedBook = Book::query()->create([
        'title' => 'Buku Sudah Kembali',
        'slug' => 'buku-sudah-kembali',
        'isbn' => '9786020010003',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $firstItem = BookItem::query()->create([
        'book_id' => $firstBook->id,
        'internal_code' => 'ITEM-HISTORY-001',
        'status' => 'borrowed',
    ]);

    $secondItem = BookItem::query()->create([
        'book_id' => $secondBook->id,
        'internal_code' => 'ITEM-HISTORY-002',
        'status' => 'borrowed',
    ]);

    $returnedItem = BookItem::query()->create([
        'book_id' => $returnedBook->id,
        'internal_code' => 'ITEM-HISTORY-003',
        'status' => 'available',
    ]);

    $activeLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(2),
    ]);

    LoanItem::query()->create([
        'loan_id' => $activeLoan->id,
        'book_item_id' => $firstItem->id,
    ]);

    LoanItem::query()->create([
        'loan_id' => $activeLoan->id,
        'book_item_id' => $secondItem->id,
    ]);

    $returnedLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_RETURNED,
        'borrowed_at' => now()->subDays(8),
        'due_at' => now()->subDays(4),
        'returned_at' => now()->subDays(3),
    ]);

    LoanItem::query()->create([
        'loan_id' => $returnedLoan->id,
        'book_item_id' => $returnedItem->id,
        'returned_at' => now()->subDays(3),
    ]);

    actingAs($member)
        ->get(route('loans.history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('loans/history')
            ->where('auth.user.id', $member->id)
            ->where('auth.user.name', $member->name)
            ->where('auth.user.email', $member->email)
            ->where('auth.user.whatsapp', '081234567890')
            ->missing('auth.user.remember_token')
            ->where('stats.total', 3)
            ->where('stats.active', 2)
            ->where('stats.overdue', 0)
            ->where('stats.returned', 1)
            ->where('returnDraft.hasActiveQr', false)
            ->where('returnDraft.selectedLoanItemIds', [])
            ->has('loans.data', 3)
            ->where('loans.data.0.bookTitle', 'Buku Sudah Kembali')
            ->where('loans.data.1.bookTitle', 'Buku Aktif Kedua')
            ->where('loans.data.2.bookTitle', 'Buku Aktif Pertama')
        );
});

it('counts overdue books individually in loan history stats', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '081234567891',
        'address' => 'Jl. Kampus Lama',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Terlambat',
        'slug' => 'penerbit-terlambat',
    ]);

    $overdueLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDays(10),
        'due_at' => now()->subDays(2),
    ]);

    foreach ([1, 2] as $index) {
        $book = Book::query()->create([
            'title' => "Buku Terlambat {$index}",
            'slug' => "buku-terlambat-{$index}",
            'isbn' => '978602001100'.$index,
            'publisher_id' => $publisher->id,
            'is_published' => true,
            'is_borrowable' => true,
        ]);

        $item = BookItem::query()->create([
            'book_id' => $book->id,
            'internal_code' => "ITEM-OVERDUE-00{$index}",
            'status' => 'borrowed',
        ]);

        LoanItem::query()->create([
            'loan_id' => $overdueLoan->id,
            'book_item_id' => $item->id,
        ]);
    }

    actingAs($member)
        ->get(route('loans.history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.total', 2)
            ->where('stats.active', 2)
            ->where('stats.overdue', 2)
            ->where('stats.returned', 0)
        );
});

it('filters loan history by query string and keeps filter props in sync', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create();
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Filter',
        'slug' => 'penerbit-filter',
    ]);

    $activeBook = Book::query()->create([
        'title' => 'Algoritma Aktif',
        'slug' => 'algoritma-aktif',
        'isbn' => '9786020012001',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $returnedBook = Book::query()->create([
        'title' => 'Basis Data Selesai',
        'slug' => 'basis-data-selesai',
        'isbn' => '9786020012002',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $activeItem = BookItem::query()->create([
        'book_id' => $activeBook->id,
        'internal_code' => 'ACTIVE-FILTER-001',
        'status' => 'borrowed',
    ]);

    $returnedItem = BookItem::query()->create([
        'book_id' => $returnedBook->id,
        'internal_code' => 'DONE-FILTER-001',
        'status' => 'available',
    ]);

    $activeLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(5),
    ]);

    LoanItem::query()->create([
        'loan_id' => $activeLoan->id,
        'book_item_id' => $activeItem->id,
    ]);

    $returnedLoan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_RETURNED,
        'borrowed_at' => now()->subDays(6),
        'due_at' => now()->subDays(3),
        'returned_at' => now()->subDays(2),
    ]);

    LoanItem::query()->create([
        'loan_id' => $returnedLoan->id,
        'book_item_id' => $returnedItem->id,
        'returned_at' => now()->subDays(2),
    ]);

    actingAs($member)
        ->get(route('loans.history', [
            'filter' => 'returned',
            'search' => 'Basis',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.filter', 'returned')
            ->where('filters.search', 'Basis')
            ->where('loans.total', 1)
            ->has('loans.data', 1)
            ->where('loans.data.0.bookTitle', 'Basis Data Selesai')
        );
});

it('shows loan history times in the application display timezone', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create();
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Zona Waktu',
        'slug' => 'penerbit-zona-waktu',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Zona Waktu',
        'slug' => 'buku-zona-waktu',
        'isbn' => '9786020012999',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $item = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'TZ-ITEM-001',
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => Carbon::parse('2026-05-28 17:30:00', 'UTC'),
        'due_at' => Carbon::parse('2026-05-30 17:30:00', 'UTC'),
    ]);

    LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $item->id,
    ]);

    actingAs($member)
        ->get(route('loans.history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('loans.data.0.borrowedAt', '29 May 2026 00:30')
            ->where('loans.data.0.dueAt', '31 May 2026 00:30')
        );
});
