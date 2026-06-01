<?php

use App\Models\Setting;
use App\Models\Skripsi;
use App\Models\User;
use App\Services\SimilarityApiService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
});

it('similarity page is displayed', function () {
    actingAs(User::factory()->create())
        ->get(route('similarity.index'))
        ->assertOk()
        ->assertSee('name="csrf-token"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('similarity')
            ->has('turnstileEnabled')
            ->has('turnstileSiteKey')
        );
});

it('similarity page keeps turnstile disabled when credentials are missing', function () {
    config()->set('services.turnstile.key', null);
    config()->set('services.turnstile.secret', null);

    Setting::query()->updateOrCreate(
        ['section' => 'integration', 'key' => 'turnstile_enabled'],
        ['value' => '1'],
    );

    actingAs(User::factory()->create())
        ->get(route('similarity.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('turnstileEnabled', false)
            ->where('turnstileSiteKey', null)
        );
});

it('similarity check requires at least five words', function () {
    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), [
            'judul' => 'Sistem informasi akademik modern',
        ])
        ->assertStatus(422)
        ->assertJson([
            'message' => 'Judul terlalu singkat. Masukkan minimal 5 kata agar pengecekan lebih akurat.',
        ]);
});

it('similarity check does not require turnstile verification when credentials are missing', function () {
    config()->set('services.turnstile.key', null);
    config()->set('services.turnstile.secret', null);

    Setting::query()->updateOrCreate(
        ['section' => 'integration', 'key' => 'turnstile_enabled'],
        ['value' => '1'],
    );

    $service = Mockery::mock(SimilarityApiService::class);
    $service->shouldReceive('checkSimilarity')
        ->once()
        ->andReturn([
            'total_found' => 0,
            'results' => [],
        ]);

    app()->instance(SimilarityApiService::class, $service);

    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), [
            'judul' => 'Analisis sentimen media sosial untuk layanan akademik kampus',
        ])
        ->assertOk()
        ->assertJson([
            'total_found' => 0,
            'results' => [],
        ]);
});

it('similarity check normalizes api results for frontend', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create([
        'id' => 99,
        'title' => 'Analisis Sentimen',
        'author_name' => 'Nadia',
        'student_id' => '2301700099',
    ]);

    $service = Mockery::mock(SimilarityApiService::class);
    $service->shouldReceive('checkSimilarity')
        ->once()
        ->andReturn([
            'total_found' => 1,
            'results' => [
                [
                    'id' => $skripsi->id,
                    'similarity_score' => 0.981,
                    'similarity_persen' => '98.1%',
                    'level' => 'sangat tinggi',
                ],
            ],
        ]);

    app()->instance(SimilarityApiService::class, $service);

    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), [
            'judul' => 'Analisis sentimen media sosial untuk layanan akademik kampus',
        ])
        ->assertOk()
        ->assertJson([
            'total_found' => 1,
            'results' => [
                [
                    'skripsi_id' => 99,
                    'judul' => 'Analisis Sentimen',
                    'nama_mahasiswa' => 'Nadia',
                    'student_id' => '2301700099',
                    'similarity_persen' => 98.1,
                    'level' => 'SANGAT TINGGI',
                    'is_local_record_found' => true,
                ],
            ],
        ]);
});

it('similarity check can match local skripsi from alternate api identifiers', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create([
        'id' => 120,
        'title' => 'Deteksi Objek Jalan Raya Berbasis YOLO',
        'author_name' => 'Rizki Maulana',
        'student_id' => '2301700120',
    ]);

    $service = Mockery::mock(SimilarityApiService::class);
    $service->shouldReceive('checkSimilarity')
        ->once()
        ->andReturn([
            'total_found' => 1,
            'results' => [
                [
                    'skripsi_id' => $skripsi->id,
                    'nim' => $skripsi->student_id,
                    'title' => 'Judul dari API yang tidak dipakai karena data lokal tersedia',
                    'author_name' => 'Nama API',
                    'similarity_score' => 0.873,
                    'level' => 'tinggi',
                ],
            ],
        ]);

    app()->instance(SimilarityApiService::class, $service);

    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), [
            'judul' => 'Deteksi objek kendaraan jalan raya berbasis deep learning realtime',
        ])
        ->assertOk()
        ->assertJson([
            'total_found' => 1,
            'results' => [
                [
                    'skripsi_id' => 120,
                    'judul' => 'Deteksi Objek Jalan Raya Berbasis YOLO',
                    'nama_mahasiswa' => 'Rizki Maulana',
                    'student_id' => '2301700120',
                    'similarity_persen' => 87.3,
                    'level' => 'TINGGI',
                    'is_local_record_found' => true,
                ],
            ],
        ]);
});

it('similarity check returns a neutral failure message when the service is unavailable internally', function () {
    $service = Mockery::mock(SimilarityApiService::class);
    $service->shouldReceive('checkSimilarity')
        ->once()
        ->andReturn(null);
    $service->shouldReceive('isHealthy')
        ->once()
        ->andReturn(true);

    app()->instance(SimilarityApiService::class, $service);

    actingAs(User::factory()->create())
        ->postJson(route('similarity.check'), [
            'judul' => 'Analisis sentimen media sosial untuk layanan akademik kampus merdeka',
        ])
        ->assertStatus(500)
        ->assertJson([
            'message' => 'Gagal melakukan pemindaian kemiripan. Jika masalah berlanjut, silakan hubungi tim pengelola perpustakaan.',
        ]);
});
