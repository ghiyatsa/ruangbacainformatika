<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use App\Support\TextStandardizer;
use Illuminate\Support\Facades\Artisan;

it('squishes text by collapsing repeated spaces', function () {
    expect(TextStandardizer::squish('  Belajar    Pemrograman   '))->toBe('Belajar Pemrograman')
        ->and(TextStandardizer::squish('   '))->toBeNull()
        ->and(TextStandardizer::squish(null))->toBeNull();
});

it('title cases words while preserving acronyms', function () {
    expect(TextStandardizer::titleCase('belajar php dan rest api'))
        ->toBe('Belajar PHP Dan REST API')
        ->and(TextStandardizer::titleCase('  clean  CODE  '))->toBe('Clean CODE')
        ->and(TextStandardizer::titleCase('node.js in action'))->toBe('Node.js In Action');
});

it('normalizes book metadata through casts on save', function () {
    $book = Book::factory()->create([
        'title' => '  belajar   pemrograman web  ',
        'subtitle' => 'panduan php',
        'description' => '  deskripsi   dengan   spasi  ganda  ',
        'ddc_code' => ' 005.133 ',
        'edition' => 'edisi revisi',
        'pages' => ' 250-300 ',
        'issn' => null,
    ]);

    $book->refresh();

    expect($book->title)->toBe('Belajar Pemrograman Web')
        ->and($book->subtitle)->toBe('Panduan PHP')
        ->and($book->description)->toBe('deskripsi dengan spasi ganda')
        ->and($book->ddc_code)->toBe('005.133')
        ->and($book->edition)->toBe('edisi revisi')
        ->and($book->pages)->toBe('250-300');
});

it('uppercases issn while keeping existing casing', function () {
    $book = Book::factory()->create([
        'title' => 'Jurnal Teknologi',
        'isbn' => null,
        'issn' => ' 1234-567x ',
    ]);

    $book->refresh();

    expect($book->issn)->toBe('1234-567X');
});

it('normalizes author and publisher names through casts', function () {
    $author = Author::factory()->create(['name' => '  john   doe  ']);
    $publisher = Publisher::factory()->create([
        'name' => ' pt   gramedia  pustaka  ',
        'city' => ' jakarta ',
    ]);

    expect($author->refresh()->name)->toBe('John Doe')
        ->and($publisher->refresh()->name)->toBe('PT Gramedia Pustaka')
        ->and($publisher->refresh()->city)->toBe('Jakarta');
});

it('standardizes existing metadata with the artisan command', function () {
    $book = Book::factory()->create([
        'title' => '  pemrograman   dasar  ',
        'description' => '  konten   berantakan ',
    ]);
    $author = Author::factory()->create(['name' => ' budi   santoso ']);
    $publisher = Publisher::factory()->create([
        'name' => '  erlangga  ',
        'city' => ' jakarta ',
    ]);

    Artisan::call('books:standardize-metadata');

    expect($book->refresh()->title)->toBe('Pemrograman Dasar')
        ->and($book->description)->toBe('konten berantakan')
        ->and($author->refresh()->name)->toBe('Budi Santoso')
        ->and($publisher->refresh()->name)->toBe('Erlangga')
        ->and($publisher->refresh()->city)->toBe('Jakarta');
});

it('keeps title case cast harmless on already clean values', function () {
    $book = Book::factory()->create([
        'title' => 'Clean Code',
        'subtitle' => 'A Handbook of Agile Software Craftsmanship',
    ]);

    $book->refresh();

    expect($book->title)->toBe('Clean Code')
        ->and($book->subtitle)->toBe('A Handbook Of Agile Software Craftsmanship');
});
