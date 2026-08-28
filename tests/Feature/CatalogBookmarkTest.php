<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

function bookmarkPayload(int $id = 1): array
{
    return [
        'catalogType' => 'book',
        'id' => $id,
        'href' => '/books/contoh-buku-'.$id,
        'title' => 'Buku Contoh '.$id,
        'subtitle' => null,
        'meta' => null,
        'year' => 2025,
        'coverImageUrl' => null,
        'kindLabel' => 'Buku',
        'statusLabel' => 'Tersedia',
    ];
}

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
});

it('requires authentication to read bookmarks', function () {
    $this->getJson(route('catalog-bookmarks.index'))->assertUnauthorized();
});

it('requires authentication to replace bookmarks', function () {
    $this->putJson(route('catalog-bookmarks.replace'), ['bookmarks' => []])
        ->assertUnauthorized();
});

it('returns an empty list for members without bookmarks', function () {
    actingAs(User::factory()->create())
        ->getJson(route('catalog-bookmarks.index'))
        ->assertOk()
        ->assertJson(['bookmarks' => []]);
});

it('replaces the member bookmark set and dedupes by catalog type and id', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->putJson(route('catalog-bookmarks.replace'), [
            'bookmarks' => [bookmarkPayload(1), bookmarkPayload(2), bookmarkPayload(1)],
        ])
        ->assertOk()
        ->assertJsonCount(2, 'bookmarks');

    expect($user->catalogBookmarks()->count())->toBe(2);

    actingAs($user)
        ->getJson(route('catalog-bookmarks.index'))
        ->assertOk()
        ->assertJsonCount(2, 'bookmarks')
        ->assertJsonPath('bookmarks.0.title', 'Buku Contoh 1');
});

it('replaces previous bookmarks on the next sync', function () {
    $user = User::factory()->create();

    $user->catalogBookmarks()->create([
        'record_key' => 'book:99',
        'payload' => bookmarkPayload(99),
    ]);

    actingAs($user)
        ->putJson(route('catalog-bookmarks.replace'), ['bookmarks' => []])
        ->assertOk();

    expect($user->fresh()->catalogBookmarks()->count())->toBe(0);
});

it('rejects malformed bookmark payloads', function () {
    actingAs(User::factory()->create())
        ->putJson(route('catalog-bookmarks.replace'), [
            'bookmarks' => [
                ['catalogType' => 'movie', 'id' => 1],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['bookmarks.0.catalogType']);
});
