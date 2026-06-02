<?php

use App\Models\Thesis;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('thesis detail page renders correctly', function () {
    $thesis = Thesis::factory()->create([
        'title' => 'Analisis Sistem Cerdas',
        'author_name' => 'Andi Pratama',
        'student_id' => '2201700020',
        'year' => 2024,
        'abstract' => 'Abstrak tesis sistem cerdas.',
        'keywords' => 'sistem cerdas, data mining',
        'view_count' => 4,
    ]);

    get(route('thesis.show', ['thesis' => $thesis->student_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('thesis/show')
            ->where('thesis.data.title', 'Analisis Sistem Cerdas')
            ->where('thesis.data.authorName', 'Andi Pratama')
            ->where('thesis.data.studentId', '2201700020')
            ->where('thesis.data.year', 2024)
            ->where('thesis.data.viewCount', 5)
            ->where('thesis.data.keywords', ['sistem cerdas', 'data mining'])
        );

    expect($thesis->fresh()->view_count)->toBe(5);
});

it('thesis detail page returns 404 for unknown nim', function () {
    get(route('thesis.show', ['thesis' => '0000000000']))
        ->assertNotFound();
});

it('thesis detail page loads related theses as deferred props', function () {
    $thesis = Thesis::factory()->create([
        'title' => 'Analisis Data Mining untuk Klasifikasi',
        'author_name' => 'Andi Pratama',
        'student_id' => '2201700100',
        'year' => 2024,
        'abstract' => 'Tesis ini membahas klasifikasi data mining.',
        'keywords' => 'data mining, klasifikasi, machine learning',
    ]);

    $relatedThesis = Thesis::factory()->create([
        'title' => 'Klasifikasi Data Mining pada Dataset Akademik',
        'author_name' => 'Nina Lestari',
        'student_id' => '2201700101',
        'year' => 2024,
        'abstract' => 'Pembahasan berfokus pada pendekatan data mining untuk klasifikasi.',
        'keywords' => 'data mining, klasifikasi, prediksi',
    ]);

    Thesis::factory()->create([
        'title' => 'Evaluasi Keamanan Jaringan',
        'student_id' => '2201700102',
        'year' => 2018,
        'keywords' => 'keamanan, jaringan, firewall',
    ]);

    get(route('thesis.show', ['thesis' => $thesis->student_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('thesis/show')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('relatedTheses')
                ->where('relatedTheses.0.id', $relatedThesis->id)
            ));
});

it('thesis detail page increments view count on each visit', function () {
    $thesis = Thesis::factory()->create(['view_count' => 2]);

    get(route('thesis.show', ['thesis' => $thesis->student_id]));
    get(route('thesis.show', ['thesis' => $thesis->student_id]));

    expect($thesis->fresh()->view_count)->toBe(4);
});
