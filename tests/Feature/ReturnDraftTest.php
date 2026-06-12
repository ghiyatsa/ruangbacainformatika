<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Publisher;
use App\Models\ReturnDraft;
use App\Models\User;
use App\Services\ReturnDraftService;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

it('generates a return qr draft from active loan history items', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '081234567892',
        'address' => 'Jl. Riwayat QR',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Return Draft',
        'slug' => 'penerbit-return-draft',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Return QR',
        'slug' => 'buku-return-qr',
        'isbn' => '9786020012001',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-RETURN-QR-001',
        'status' => 'borrowed',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_BORROWED,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDays(2),
    ]);

    $loanItem = LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $bookItem->id,
    ]);

    actingAs($member)
        ->post(route('loans.history.qr'), [
            'loan_item_ids' => [$loanItem->id],
        ])
        ->assertRedirect(route('loans.history'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'QR pengembalian berhasil dibuat.');

    $draft = ReturnDraft::query()->whereBelongsTo($member)->latest('id')->first();

    expect($draft)->toBeInstanceOf(ReturnDraft::class)
        ->and($draft?->status)->toBe(ReturnDraft::STATUS_PENDING)
        ->and($draft?->items()->count())->toBe(1)
        ->and($draft?->selected_loan_item_ids)->toBe([$loanItem->id])
        ->and(session('return_request_qr.payload'))
        ->toBeString()
        ->toHaveLength(51)
        ->toStartWith(ReturnDraftService::SHORT_TOKEN_PREFIX)
        ->not->toStartWith(ReturnDraftService::TOKEN_PREFIX);

    actingAs($member)
        ->get(route('loans.history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('returnDraft.hasActiveQr', true)
            ->where('returnDraft.itemsCount', 1)
            ->where('returnDraft.selectedLoanItemIds', [$loanItem->id])
            ->where('returnDraft.items.0.bookTitle', 'Buku Return QR')
            ->where('returnDraft.qrCodeSvg', fn (?string $value): bool => filled($value))
        );
});

it('rejects creating a return qr draft for books that are already returned', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '081234567894',
        'address' => 'Jl. Return Lama',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Return Lama',
        'slug' => 'penerbit-return-lama',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Sudah Kembali',
        'slug' => 'buku-sudah-kembali-qr',
        'isbn' => '9786020012002',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    $bookItem = BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-RETURN-QR-002',
        'status' => 'available',
    ]);

    $loan = Loan::query()->create([
        'user_id' => $member->id,
        'status' => Loan::STATUS_RETURNED,
        'borrowed_at' => now()->subDays(6),
        'due_at' => now()->subDays(3),
        'returned_at' => now()->subDays(2),
    ]);

    $loanItem = LoanItem::query()->create([
        'loan_id' => $loan->id,
        'book_item_id' => $bookItem->id,
        'returned_at' => now()->subDays(2),
    ]);

    actingAs($member)
        ->from(route('loans.history'))
        ->post(route('loans.history.qr'), [
            'loan_item_ids' => [$loanItem->id],
        ])
        ->assertRedirect(route('loans.history'))
        ->assertSessionHasErrors('loan_item_ids');

    expect(ReturnDraft::query()->whereBelongsTo($member)->exists())->toBeFalse();
});
