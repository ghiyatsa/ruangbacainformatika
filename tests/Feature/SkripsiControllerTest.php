<?php

use App\Models\Skripsi;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('skripsi index page loads skripsis as deferred props', function () {
    Queue::fake();

    Skripsi::factory()->create([
        'title' => 'Analisis Sistem Informasi Perpustakaan',
        'author_name' => 'Budi Santoso',
        'student_id' => '1234567890',
        'year' => 2024,
    ]);

    get(route('skripsi.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/index')
            ->where('total', 1)
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('skripsis.data', 1)
                ->where('skripsis.data.0.title', 'Analisis Sistem Informasi Perpustakaan')
                ->where('skripsis.data.0.studentId', '1234567890')
            ));
});

it('skripsi detail page renders correctly', function () {
    Queue::fake();
    $skripsi = Skripsi::factory()->create([
        'title' => 'Analisis Sistem Informasi Perpustakaan',
        'author_name' => 'Budi Santoso',
        'student_id' => '1234567890',
        'year' => 2024,
        'abstract' => 'Abstrak penelitian ini membahas sistem informasi perpustakaan.',
        'keywords' => 'sistem, informasi, perpustakaan',
        'view_count' => 3,
    ]);

    get(route('skripsi.show', ['skripsi' => $skripsi->student_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/show')
            ->where('skripsi.data.title', 'Analisis Sistem Informasi Perpustakaan')
            ->where('skripsi.data.authorName', 'Budi Santoso')
            ->where('skripsi.data.studentId', '1234567890')
            ->where('skripsi.data.year', 2024)
            ->where('skripsi.data.viewCount', 4)
        );

    expect($skripsi->fresh()->view_count)->toBe(4);
});

it('skripsi detail page returns 404 for unknown nim', function () {
    get(route('skripsi.show', ['skripsi' => '0000000000']))
        ->assertNotFound();
});

it('skripsi detail page exposes keywords as array', function () {
    Queue::fake();
    $skripsi = Skripsi::factory()->create([
        'keywords' => 'machine learning, deep learning, neural network',
    ]);

    get(route('skripsi.show', ['skripsi' => $skripsi->student_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/show')
            ->where('skripsi.data.keywords', ['machine learning', 'deep learning', 'neural network'])
        );
});

it('skripsi detail page returns empty keywords array when none set', function () {
    Queue::fake();
    $skripsi = Skripsi::factory()->create(['keywords' => null]);

    get(route('skripsi.show', ['skripsi' => $skripsi->student_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/show')
            ->where('skripsi.data.keywords', [])
        );
});

it('skripsi detail page loads related skripsis as deferred props', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create([
        'title' => 'Analisis Sistem Informasi Akademik',
        'author_name' => 'Budi Santoso',
        'student_id' => '2301700001',
        'year' => 2024,
        'abstract' => 'Penelitian ini membahas sistem informasi akademik berbasis web.',
        'keywords' => 'sistem informasi, akademik, web',
    ]);

    $relatedSkripsi = Skripsi::factory()->create([
        'title' => 'Perancangan Sistem Informasi Akademik',
        'author_name' => 'Siti Rahma',
        'student_id' => '2301700002',
        'year' => 2024,
        'abstract' => 'Fokus penelitian pada pengembangan sistem informasi akademik.',
        'keywords' => 'sistem informasi, akademik, kampus',
    ]);

    Skripsi::factory()->create([
        'title' => 'Analisis Jaringan Komputer',
        'student_id' => '2301700003',
        'year' => 2019,
        'keywords' => 'jaringan, router, switch',
    ]);

    get(route('skripsi.show', ['skripsi' => $skripsi->student_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/show')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('relatedSkripsis')
                ->where('relatedSkripsis.0.id', $relatedSkripsi->id)
            ));
});

it('skripsi detail page increments view count on each visit', function () {
    Queue::fake();
    $skripsi = Skripsi::factory()->create(['view_count' => 0]);

    get(route('skripsi.show', ['skripsi' => $skripsi->student_id]));
    get(route('skripsi.show', ['skripsi' => $skripsi->student_id]));

    expect($skripsi->fresh()->view_count)->toBe(2);
});
