<?php

use App\Models\Book;
use App\Models\Publisher;
use App\Services\Search\SearchTermCorrector;

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
