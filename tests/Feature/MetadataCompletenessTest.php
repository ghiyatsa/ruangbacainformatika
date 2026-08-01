<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Support\MetadataCompleteness;

function makeFullyCuratedBook(): Book
{
    $publisher = Publisher::factory()->create();
    $book = Book::factory()->create([
        'title' => 'Buku Lengkap',
        'subtitle' => 'Subjudul Buku',
        'isbn' => '9786020000001',
        'issn' => null,
        'ddc_code' => '005.133',
        'description' => 'Deskripsi lengkap.',
        'cover_image' => 'books/covers/cover.jpg',
        'language' => 'Indonesia',
        'published_year' => 2024,
        'publisher_id' => $publisher->id,
    ]);

    $book->authors()->attach(Author::factory()->create());
    $book->categories()->attach(Category::factory()->create());

    return $book->load(['authors', 'categories']);
}

function makeSparselyFilledBook(): Book
{
    $book = Book::factory()->create([
        'title' => 'Buku Minim',
        'subtitle' => null,
        'isbn' => '9786020000002',
        'issn' => null,
        'ddc_code' => null,
        'description' => null,
        'cover_image' => null,
        'language' => '',
        'published_year' => null,
        'publisher_id' => Publisher::factory()->create()->id,
    ]);

    return $book->load(['authors', 'categories']);
}

it('scores a fully curated book as lengkap', function () {
    $book = makeFullyCuratedBook();

    expect($book->metadata_score)->toBe(100)
        ->and($book->metadata_level)->toBe(MetadataCompleteness::LEVEL_LENGKAP)
        ->and($book->metadata_missing)->toBe([]);
});

it('lists missing metadata elements for a sparse book', function () {
    $book = makeSparselyFilledBook();

    expect($book->metadata_missing)->toBe([
        'Kode DDC',
        'Deskripsi',
        'Sampul',
        'Bahasa',
        'Tahun terbit',
        'Penulis',
        'Kategori',
    ]);
});

it('classifies score levels consistently', function (int $score, string $level) {
    expect(MetadataCompleteness::levelForScore($score))->toBe($level);
})->with([
    [100, MetadataCompleteness::LEVEL_LENGKAP],
    [75, MetadataCompleteness::LEVEL_SEBAGIAN],
    [50, MetadataCompleteness::LEVEL_SEBAGIAN],
    [25, MetadataCompleteness::LEVEL_KURANG],
]);

it('filters books by metadata level', function () {
    $lengkap = makeFullyCuratedBook();
    $kurang = makeSparselyFilledBook();

    expect(Book::query()->metadataLevel(MetadataCompleteness::LEVEL_LENGKAP)->pluck('id'))
        ->toContain($lengkap->id)
        ->not->toContain($kurang->id);

    expect(Book::query()->metadataLevel(MetadataCompleteness::LEVEL_KURANG)->pluck('id'))
        ->toContain($kurang->id)
        ->not->toContain($lengkap->id);
});

it('groups books by metadata level with aggregate counts', function () {
    makeFullyCuratedBook();
    makeSparselyFilledBook();

    $rows = Book::query()
        ->selectRaw('COUNT(*) AS total, ('.MetadataCompleteness::filledSql().') AS filled')
        ->groupBy('filled')
        ->get();

    expect($rows->sum('total'))->toBe(Book::query()->count());
});
