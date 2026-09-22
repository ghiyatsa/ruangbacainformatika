<?php

use App\Models\Post;
use App\Models\User;
use Database\Seeders\PostSeeder;

it('seeds article content without paragraphs wrapping block elements', function () {
    User::factory()->create(['is_approved' => true]);

    app(PostSeeder::class)->run();

    $contents = Post::query()->pluck('content');

    expect($contents)->not->toBeEmpty();

    foreach ($contents as $content) {
        // <p> yang membungkus <ul>/<ol>/<pre> adalah HTML tidak valid dan
        // membuat sanitizer memunculkan paragraf kosong di halaman artikel.
        expect($content)
            ->not->toMatch('/<p>[^<]*\n?<(ul|ol|pre)>/')
            ->not->toMatch('/<\/(ul|ol|pre)>\s*<\/p>/');
    }
});

it('seeds every article with a non-empty summary', function () {
    User::factory()->create(['is_approved' => true]);

    app(PostSeeder::class)->run();

    expect(Post::query()->whereNull('summary')->orWhere('summary', '')->count())->toBe(0);
});
