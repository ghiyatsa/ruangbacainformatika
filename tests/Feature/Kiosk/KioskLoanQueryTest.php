<?php

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Publisher;
use App\Models\User;
use App\Services\KioskLoanService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Regresi N+1 pada KioskLoanService::borrow().
 *
 * Sebelumnya `Book::query()->find()` dipanggil di dalam foreach sehingga
 * jumlah query ke tabel `books` tumbuh seiring jumlah buku yang dipinjam.
 * Test ini mengunci jumlahnya agar tetap konstan.
 *
 * Catatan: query `whereHas('book', ...)` menghasilkan subquery `exists
 * (select ... from "books" ...)` per buku — itu melekat pada desain query dan
 * bukan bagian dari N+1 yang diperbaiki. Karena itu query ber-`exists`
 * dikecualikan dari hitungan.
 */
beforeEach(function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
});

function loanQueryMember(): User
{
    $member = User::factory()->create([
        'email' => 'anggota.'.random_int(100000000, 999999999).'@mhs.unimal.ac.id',
        'whatsapp' => '08'.str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus Bukit Indah',
    ]);
    $member->assignRole('member');

    return $member;
}

function loanQueryBook(): Book
{
    $publisher = Publisher::query()->create([
        'name' => 'Penerbit Uji '.random_int(100000, 999999),
        'slug' => 'penerbit-uji-'.random_int(100000, 999999),
    ]);

    $book = Book::query()->create([
        'title' => 'Buku Uji '.random_int(100000, 999999),
        'slug' => 'buku-uji-'.random_int(100000, 999999),
        'isbn' => '978602'.str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
        'publisher_id' => $publisher->id,
        'is_published' => true,
        'is_borrowable' => true,
    ]);

    BookItem::query()->create([
        'book_id' => $book->id,
        'internal_code' => 'ITEM-'.random_int(100000, 999999),
        'status' => 'available',
    ]);

    return $book;
}

/**
 * Jumlah query pemuatan tabel `books` (di luar subquery exists dari whereHas).
 */
function countBookLoadQueries(): int
{
    return collect(DB::getQueryLog())
        ->filter(function (array $entry): bool {
            $sql = $entry['query'];

            return str_contains($sql, 'from "books"')
                && ! str_contains($sql, 'exists');
        })
        ->count();
}

it('tidak menambah query ke tabel books saat meminjam lebih banyak buku', function () {
    $service = app(KioskLoanService::class);

    // Pinjam 1 buku -> baseline.
    $single = loanQueryBook();
    DB::enableQueryLog();
    $service->borrow(loanQueryMember()->nim(), [$single->id]);
    $queriesForOne = countBookLoadQueries();
    DB::disableQueryLog();
    DB::flushQueryLog();

    // Pinjam 3 buku sekaligus -> jumlah query pemuatan harus tetap konstan.
    $many = collect(range(1, 3))->map(fn (): Book => loanQueryBook())->all();
    DB::enableQueryLog();
    $service->borrow(loanQueryMember()->nim(), collect($many)->pluck('id')->all());
    $queriesForMany = countBookLoadQueries();
    DB::disableQueryLog();

    // Konstan (tidak tumbuh seiring jumlah buku): 1 query batch + 1 eager load.
    expect($queriesForMany)->toBe($queriesForOne)
        ->and($queriesForMany)->toBeLessThan(count($many));
});
