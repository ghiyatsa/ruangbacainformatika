<?php

use App\Models\Book;
use App\Models\Publisher;
use App\Services\Search\SearchTermCorrector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('corrects simple typos from the dictionary', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Buku Metode Penelitian',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    expect(app(SearchTermCorrector::class)->correctQuery('metde'))->toBe('metode');
});

it('returns null when the query is already correct', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Buku Metode Penelitian',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    expect(app(SearchTermCorrector::class)->correctQuery('metode'))->toBeNull();
});

it('does not correct short tokens', function () {
    expect(app(SearchTermCorrector::class)->correctQuery('ab'))->toBeNull();
});

it('menyertakan kata dari seluruh alfabet di kamus', function () {
    $penerbit = Publisher::factory()->create();

    // Kata berawalan akhir alfabet: pada kamus lama yang dipotong secara
    // alfabetis, kata seperti ini tidak pernah masuk sehingga koreksi
    // ejaannya mustahil dilakukan.
    Book::withoutEvents(fn () => Book::factory()->create([
        'title' => 'Teknik Pemrograman Zigzag',
        'is_published' => true,
        'publisher_id' => $penerbit->id,
    ]));

    $kamus = app(SearchTermCorrector::class)->buildDictionary();

    expect($kamus)->toContain('pemrograman')
        ->and($kamus)->toContain('zigzag');
});

it('mengoreksi ejaan kata yang berawalan akhir alfabet', function () {
    $penerbit = Publisher::factory()->create();

    Book::withoutEvents(fn () => Book::factory()->create([
        'title' => 'Teknik Pemrograman Lanjut',
        'is_published' => true,
        'publisher_id' => $penerbit->id,
    ]));

    // Satu huruf hilang, dan kata berawalan "p" tidak pernah ada pada
    // kamus lama.
    expect(app(SearchTermCorrector::class)->correctQuery('pemrogaman'))
        ->toBe('pemrograman');
});

it('memecah istilah gabungan yang tidak dikenali kamus', function () {
    $penerbit = Publisher::factory()->create();

    Book::withoutEvents(fn () => Book::factory()->create([
        'title' => 'Basis Data Dan Jaringan Komputer',
        'is_published' => true,
        'publisher_id' => $penerbit->id,
    ]));

    expect(app(SearchTermCorrector::class)->correctQuery('basisdata'))
        ->toBe('basis data');
});

it('tidak memecah istilah yang sudah dikenali kamus', function () {
    $penerbit = Publisher::factory()->create();

    Book::withoutEvents(fn () => Book::factory()->create([
        'title' => 'Basis Data Dan Jaringan Komputer',
        'is_published' => true,
        'publisher_id' => $penerbit->id,
    ]));

    // "jaringan" sudah kata yang dikenali sehingga tidak boleh diubah.
    expect(app(SearchTermCorrector::class)->correctQuery('jaringan'))->toBeNull();
});

it('membangun kamus secara bertahap, bukan satu query besar', function () {
    $penerbit = Publisher::factory()->create();

    Book::withoutEvents(fn () => Book::factory()->count(3)->create([
        'is_published' => true,
        'publisher_id' => $penerbit->id,
    ]));

    Cache::flush();

    DB::enableQueryLog();
    app(SearchTermCorrector::class)->buildDictionary();
    $bookQuery = collect(DB::getQueryLog())
        ->pluck('query')
        ->first(fn (string $q): bool => str_contains($q, 'from "books"'));
    DB::disableQueryLog();

    // lazy() mengambil data per-batch (limit + offset), sedangkan pluck()
    // menarik seluruh kolom sekaligus tanpa limit.
    expect($bookQuery)->not->toBeNull()
        ->and($bookQuery)->toContain('limit');
});
