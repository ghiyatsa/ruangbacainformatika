<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Publisher;
use App\Models\User;
use App\Services\KioskLoanService;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

it('members must fill whatsapp before borrowing books', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => null,
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Test',
        'slug' => 'penerbit-test',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Test',
        'slug' => 'buku-test',
        'isbn' => '9786020000001',
        'publisher_id' => $publisher->id,
        'is_published' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-001',
        'status' => 'available',
    ]);

    $service = app(KioskLoanService::class);

    expect(fn () => $service->borrow($member->nim(), ['9786020000001']))
        ->toThrow(ValidationException::class, 'Nomor WhatsApp wajib diisi pada profil sebelum meminjam buku.');
});

it('books marked as not borrowable cannot be borrowed', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp' => '08123456789',
    ]);
    $member->assignRole('member');

    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Test',
        'slug' => 'penerbit-test',
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Referensi',
        'slug' => 'buku-referensi',
        'isbn' => '9786020000002',
        'issn' => '1234-5678',
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => false,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-002',
        'status' => 'available',
    ]);

    $service = app(KioskLoanService::class);

    expect(fn () => $service->borrow($member->nim(), ['9786020000002']))
        ->toThrow(ValidationException::class, 'Buku dengan ISBN 9786020000002 ditandai tidak boleh dipinjam.');
});
