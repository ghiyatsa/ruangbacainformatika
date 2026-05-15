<?php

use App\Models\Skripsi;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('skripsi catalog page renders results', function () {
    Queue::fake();
    Skripsi::factory()->create([

        'title' => 'Analisis Sistem Informasi',
        'author_name' => 'Budi Santoso',
        'year' => 2024,
    ]);

    get(route('skripsi.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/index')
            ->where('total', 1)
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('skripsis.data', 1)
                ->where('skripsis.data.0.title', 'Analisis Sistem Informasi')
            ));
});

test('skripsi catalog page filters by search keyword', function () {
    Queue::fake();
    Skripsi::factory()->create(['title' => 'Sistem Informasi Akademik']);

    Skripsi::factory()->create(['title' => 'Jaringan Komputer']);

    get(route('skripsi.index', ['search' => 'Akademik']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/index')
            ->where('filters.search', 'Akademik')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('skripsis.data', 1)
                ->where('skripsis.data.0.title', 'Sistem Informasi Akademik')
            ));
});

test('skripsi catalog page filters by year', function () {
    Queue::fake();
    Skripsi::factory()->create([

        'title' => 'Skripsi 2024',
        'year' => 2024,
    ]);
    Skripsi::factory()->create([
        'title' => 'Skripsi 2023',
        'year' => 2023,
    ]);

    get(route('skripsi.index', ['year' => 2024]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/index')
            ->where('filters.year', 2024)
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('skripsis.data', 1)
                ->where('skripsis.data.0.title', 'Skripsi 2024')
            ));
});

test('skripsi catalog page returns the requested pagination page', function () {
    Queue::fake();

    foreach (range(1, 21) as $number) {
        Skripsi::factory()->create([
            'title' => sprintf('Skripsi %02d', $number),
            'year' => 2024,
        ]);
    }

    get(route('skripsi.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/index')
            ->where('total', 21)
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->where('skripsis.current_page', 2)
                ->where('skripsis.next_page_url', null)
                ->has('skripsis.data', 1)
                ->where('skripsis.data.0.title', 'Skripsi 21')
            ));
});
