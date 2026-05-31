<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Publisher;
use App\Models\Skripsi;
use App\Models\Thesis;
use Database\Seeders\LocalDevelopmentSeeder;
use Illuminate\Support\Facades\Queue;

it('seeds local development demo content', function () {
    Queue::fake();

    app(LocalDevelopmentSeeder::class)->run();

    expect(Author::query()->count())->toBeGreaterThan(0)
        ->and(Publisher::query()->count())->toBeGreaterThan(0)
        ->and(Book::query()->count())->toBe(6)
        ->and(Skripsi::query()->count())->toBe(5)
        ->and(Thesis::query()->count())->toBe(2)
        ->and(InternshipReport::query()->count())->toBe(2);
});

it('does not duplicate local development demo content when rerun', function () {
    Queue::fake();

    app(LocalDevelopmentSeeder::class)->run();
    app(LocalDevelopmentSeeder::class)->run();

    expect(Book::query()->count())->toBe(6)
        ->and(Skripsi::query()->count())->toBe(5)
        ->and(Thesis::query()->count())->toBe(2)
        ->and(InternshipReport::query()->count())->toBe(2);
});
