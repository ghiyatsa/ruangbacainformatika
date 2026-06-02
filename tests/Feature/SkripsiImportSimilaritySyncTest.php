<?php

use App\Filament\Imports\SkripsiImporter;
use App\Jobs\SyncSkripsiToSimilarity;
use App\Listeners\DispatchSimilaritySyncAfterSkripsiImport;
use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use App\Models\User;
use Filament\Actions\Imports\Events\ImportCompleted;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

it('does not queue similarity sync jobs while skripsi rows are being imported', function () {
    Queue::fake();

    $user = User::factory()->create();
    $import = Import::query()->create([
        'file_name' => 'skripsi.csv',
        'file_path' => 'imports/skripsi.csv',
        'importer' => SkripsiImporter::class,
        'processed_rows' => 0,
        'total_rows' => 1,
        'successful_rows' => 0,
        'user_id' => $user->id,
    ]);

    $importer = new SkripsiImporter($import, [
        'title' => 'Judul',
        'author_name' => 'Nama',
        'student_id' => 'NIM',
        'year' => 'Tahun',
        'abstract' => 'Abstrak',
        'keywords' => 'Kata Kunci',
    ], []);

    $importer([
        'Judul' => 'Analisis Sistem Informasi Akademik',
        'Nama' => 'Budi Santoso',
        'NIM' => '13520001',
        'Tahun' => 2024,
        'Abstrak' => 'Abstrak uji impor.',
        'Kata Kunci' => 'sistem informasi',
    ]);

    $skripsi = Skripsi::query()->where('student_id', '13520001')->firstOrFail();

    Queue::assertNotPushed(SyncSkripsiToSimilarity::class);

    expect(SimilaritySyncStatus::query()
        ->where('source_skripsi_id', $skripsi->id)
        ->value('status'))
        ->toBe(SimilaritySyncStatus::STATUS_PENDING)
        ->and(Cache::get(SkripsiImporter::importedIdsCacheKey($import->id)))
        ->toBe([$skripsi->id]);
});

it('queues similarity sync jobs after a skripsi import is completed', function () {
    Queue::fake();

    $skripsis = Skripsi::withoutEvents(fn () => Skripsi::factory()->count(2)->create());
    $user = User::factory()->create();
    $import = Import::query()->create([
        'file_name' => 'skripsi.csv',
        'file_path' => 'imports/skripsi.csv',
        'importer' => SkripsiImporter::class,
        'processed_rows' => 2,
        'total_rows' => 2,
        'successful_rows' => 2,
        'user_id' => $user->id,
        'completed_at' => now(),
    ]);

    Cache::put(SkripsiImporter::importedIdsCacheKey($import->id), $skripsis->pluck('id')->all(), now()->addDay());

    app(DispatchSimilaritySyncAfterSkripsiImport::class)->handle(new ImportCompleted($import, [], []));

    Queue::assertPushed(SyncSkripsiToSimilarity::class, 2);
    Queue::assertPushed(SyncSkripsiToSimilarity::class, fn (SyncSkripsiToSimilarity $job): bool => $job->skripsiId === $skripsis[0]->id);
    Queue::assertPushed(SyncSkripsiToSimilarity::class, fn (SyncSkripsiToSimilarity $job): bool => $job->skripsiId === $skripsis[1]->id);

    expect(Cache::get(SkripsiImporter::importedIdsCacheKey($import->id)))->toBeNull();
});
