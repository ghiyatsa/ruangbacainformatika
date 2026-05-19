<?php

use App\Models\Skripsi;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('skripsi detail page renders correctly', function () {
    Queue::fake();
    $skripsi = Skripsi::factory()->create([

        'title' => 'Analisis Sistem Informasi Perpustakaan',
        'author_name' => 'Budi Santoso',
        'student_id' => '1234567890',
        'year' => 2024,
        'abstract' => 'Abstrak penelitian ini membahas sistem informasi perpustakaan.',
        'keywords' => 'sistem, informasi, perpustakaan',
    ]);

    get(route('skripsi.show', ['skripsi' => $skripsi->student_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('skripsi/show')
            ->where('skripsi.data.title', 'Analisis Sistem Informasi Perpustakaan')
            ->where('skripsi.data.authorName', 'Budi Santoso')
            ->where('skripsi.data.studentId', '1234567890')
            ->where('skripsi.data.year', 2024)
        );
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
