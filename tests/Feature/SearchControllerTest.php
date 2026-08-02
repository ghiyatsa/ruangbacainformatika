<?php

use App\Models\Book;
use App\Models\Post;
use App\Models\Publisher;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;

beforeEach(function () {
    Queue::fake();
});

it('renders search index page with empty state when no query', function () {
    get(route('search'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('search/index')
                ->where('query', '')
                ->where('results.books', [])
                ->where('results.posts', [])
                ->where('results.skripsis', [])
                ->where('results.internshipReports', [])
                ->where('results.theses', [])
        );
});

it('returns empty suggestions when query is empty', function () {
    get(route('search.suggestions', ['q' => '']))
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

    get(route('search.suggestions', ['q' => 'Laravel']))
        ->assertOk()
        ->assertJsonCount(2)
        ->assertExactJson([
            'laravel untuk komunitas kampus',
            'laravel untuk pemula',
        ]);
});

it('formats suggestions like Google Autocomplete', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Sistem: Monitoring & Kontrol $u#u! Kelembapan Dan Gas Amonia',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    get(route('search.suggestions', ['q' => 'kon']))
        ->assertOk()
        ->assertExactJson([
            'kontrol uu kelembapan dan',
        ]);
});

it('handles multi-word non-contiguous autocomplete search queries', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Code a Handbook for developers of laravel',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    get(route('search.suggestions', ['q' => 'code handbook']))
        ->assertOk()
        ->assertExactJson([
            'code a handbook for developers of',
        ]);
});

it('returns search results matching query on index page and records history', function () {
    $publisher = Publisher::factory()->create();

    Book::factory()->create([
        'title' => 'Pemrograman Laravel untuk Pemula',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    Post::factory()->published()->create([
        'title' => 'Laravel untuk Komunitas Kampus',
        'summary' => 'Catatan singkat tentang Laravel.',
    ]);

    get(route('search', ['q' => 'Laravel']))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('search/index')
                ->where('query', 'Laravel')
                ->has('results.books', 1)
                ->has('results.posts', 1)
        );

    assertDatabaseHas('search_histories', [
        'query' => 'Laravel',
        'hits' => 1,
    ]);
});

it('increments search history hits when query is clicked', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Pemrograman Laravel untuk Pemula',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    // Initial search
    get(route('search', ['q' => 'Laravel']));
    assertDatabaseHas('search_histories', [
        'query' => 'Laravel',
        'hits' => 1,
    ]);

    // Clicked search
    get(route('search', ['q' => 'Laravel']), ['X-Search-Clicked' => '1']);
    assertDatabaseHas('search_histories', [
        'query' => 'Laravel',
        'hits' => 2,
    ]);
});

it('records search history even when no results found', function () {
    get(route('search', ['q' => 'dkanlknda']));

    assertDatabaseHas('search_histories', [
        'query' => 'dkanlknda',
        'hits' => 1,
    ]);
});

it('does not record search history for queries shorter than two characters', function () {
    get(route('search', ['q' => 'a']));

    assertDatabaseMissing('search_histories', [
        'query' => 'a',
    ]);
});

it('does not treat like wildcards as search terms', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Buku ABC Pemrograman',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    get(route('search', ['q' => '%']))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('search/index')
                ->where('results.books', [])
        );

    assertDatabaseMissing('search_histories', [
        'query' => '%',
    ]);
});

it('returns empty suggestions when query is only a like wildcard', function () {
    get(route('search.suggestions', ['q' => '%']))
        ->assertOk()
        ->assertExactJson([]);
});

it('returns corrected results for queries with typos', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Buku Metode Penelitian',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    get(route('search', ['q' => 'metde']))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('search/index')
                ->where('results.books.0.title', 'Buku Metode Penelitian')
        );
});

it('suggests corrected titles for queries with typos', function () {
    $publisher = Publisher::factory()->create();
    Book::factory()->create([
        'title' => 'Buku Metode Penelitian',
        'is_published' => true,
        'publisher_id' => $publisher->id,
    ]);

    get(route('search.suggestions', ['q' => 'metde']))
        ->assertOk()
        ->assertExactJson(['buku metode penelitian']);
});
