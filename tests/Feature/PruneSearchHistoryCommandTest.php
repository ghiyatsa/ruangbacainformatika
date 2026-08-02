<?php

use App\Models\SearchHistory;

use function Pest\Laravel\artisan;

it('prunes search history older than the retention period', function () {
    $old = SearchHistory::query()->create(['query' => 'lama', 'hits' => 1]);
    $old->forceFill(['created_at' => now()->subDays(100)])->save();

    $recent = SearchHistory::query()->create(['query' => 'baru', 'hits' => 1]);
    $recent->forceFill(['created_at' => now()->subDay()])->save();

    artisan('app:prune-search-history')
        ->expectsOutput('Berhasil menghapus 1 riwayat pencarian yang sudah lama.')
        ->assertExitCode(0);

    expect(SearchHistory::find($old->id))->toBeNull();
    expect(SearchHistory::find($recent->id))->not->toBeNull();
});
