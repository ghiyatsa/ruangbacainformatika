<?php

use App\Models\Skripsi;
use App\Services\SimilarityApiService;

use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

test('similarity page is displayed', function () {
    get(route('similarity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('similarity'));
});

test('similarity check requires at least five words', function () {
    postJson(route('similarity.check'), [
        'judul' => 'Sistem informasi akademik modern',
    ])
        ->assertStatus(422)
        ->assertJson([
            'message' => 'Judul terlalu singkat. Masukkan minimal 3 kata agar pengecekan lebih akurat.',
        ]);
});

test('similarity check normalizes api results for frontend', function () {
    $skripsi = Skripsi::factory()->create([
        'id' => 99,
        'student_id' => '2301700099',
    ]);

    $service = Mockery::mock(SimilarityApiService::class);
    $service->shouldReceive('checkSimilarity')
        ->once()
        ->andReturn([
            'total_found' => 1,
            'results' => [
                [
                    'skripsi_id' => $skripsi->id,
                    'judul' => 'Analisis Sentimen',
                    'nama_mahasiswa' => 'Nadia',
                    'similarity_persen' => '98.1%',
                    'level' => 'sangat tinggi',
                ],
            ],
        ]);

    app()->instance(SimilarityApiService::class, $service);

    postJson(route('similarity.check'), [
        'judul' => 'Analisis sentimen media sosial untuk layanan akademik kampus',
    ])
        ->assertOk()
        ->assertJson([
            'total_found' => 1,
            'results' => [
                [
                    'skripsi_id' => 99,
                    'student_id' => '2301700099',
                    'similarity_persen' => 98.1,
                    'level' => 'SANGAT TINGGI',
                ],
            ],
        ]);
});
