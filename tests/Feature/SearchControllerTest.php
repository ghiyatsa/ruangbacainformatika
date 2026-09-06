<?php

use App\Models\Book;
use App\Models\Publisher;
use App\Models\SearchHistory;
use App\Models\Skripsi;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\get;

beforeEach(function () {
    Queue::fake();
});

it('returns empty suggestions when query is empty', function () {
    get(route('search.suggestions', ['q' => '', 'legacy' => 1]))
        ->assertOk()
        ->assertExactJson([]);
});

it('returns suggestions matching query from search history', function () {
    SearchHistory::create([
        'query' => 'Pemrograman Laravel untuk Pemula',
        'hits' => 5,
    ]);

    SearchHistory::create([
        'query' => 'Laravel untuk Komunitas Kampus',
        'hits' => 10,
    ]);

    SearchHistory::create([
        'query' => 'Belajar React JS',
        'hits' => 2,
    ]);

    get(route('search.suggestions', ['q' => 'Laravel', 'legacy' => 1]))
        ->assertOk()
        ->assertJsonCount(2)
        ->assertExactJson([
            'laravel untuk komunitas kampus',
            'pemrograman laravel untuk pemula',
        ]);
});

it('formats suggestions like Google Autocomplete', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Sistem: Monitoring & Kontrol $u#u! Kelembapan Dan Gas Amonia',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    get(route('search.suggestions', ['q' => 'kon', 'legacy' => 1]))
        ->assertOk()
        ->assertExactJson([
            'sistem monitoring  kontrol uu kelembapan dan gas amonia',
        ]);
});

it('handles multi-word non-contiguous autocomplete search queries', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Code a Handbook for developers of laravel',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    get(route('search.suggestions', ['q' => 'code handbook', 'legacy' => 1]))
        ->assertOk()
        ->assertExactJson([
            'code a handbook for developers of laravel',
        ]);
});

it('returns empty suggestions when query is only a like wildcard', function () {
    get(route('search.suggestions', ['q' => '%', 'legacy' => 1]))
        ->assertOk()
        ->assertExactJson([]);
});

it('suggests corrected titles for queries with typos', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Buku Metode Penelitian',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    get(route('search.suggestions', ['q' => 'metde', 'legacy' => 1]))
        ->assertOk()
        ->assertExactJson(['buku metode penelitian']);
});

it('filters out skripsi quick results for guest or non-member', function () {
    Skripsi::factory()->create([
        'title' => 'Sistem Rekomendasi Buku',
        'student_id' => '1234567890',
        'author_name' => 'John Doe',
    ]);

    get(route('search.suggestions', ['q' => 'Rekomendasi']))
        ->assertOk()
        ->assertJsonPath('quickResults.skripsi', []);
});
