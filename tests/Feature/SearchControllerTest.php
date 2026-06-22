<?php

use App\Models\Book;
use App\Models\Post;
use App\Models\Publisher;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;

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
        // Ordered by hits desc
        ->assertExactJson([
            'Laravel untuk Komunitas Kampus',
            'Pemrograman Laravel untuk Pemula',
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

    $this->assertDatabaseHas('search_histories', [
        'query' => 'Laravel',
        'hits' => 1,
    ]);
});
