<?php

use App\Models\ActivityLog;
use App\Models\VisitLog;

use function Pest\Laravel\artisan;

it('prunes activity and visit logs older than the retention period', function () {
    $oldActivity = ActivityLog::query()->create([
        'action' => 'catalog.book.created',
        'description' => 'lama',
    ]);
    $oldActivity->forceFill(['created_at' => now()->subDays(200)])->save();

    $oldVisit = VisitLog::factory()->create();
    $oldVisit->forceFill(['created_at' => now()->subDays(200)])->save();

    $recentActivity = ActivityLog::query()->create([
        'action' => 'catalog.book.created',
        'description' => 'baru',
    ]);
    $recentActivity->forceFill(['created_at' => now()->subDay()])->save();

    $recentVisit = VisitLog::factory()->create();
    $recentVisit->forceFill(['created_at' => now()->subDay()])->save();

    artisan('app:prune-audit-logs')
        ->expectsOutput('Berhasil menghapus 1 log aktivitas dan 1 log kunjungan yang sudah lama.')
        ->assertExitCode(0);

    expect(ActivityLog::find($oldActivity->id))->toBeNull();
    expect(ActivityLog::find($recentActivity->id))->not->toBeNull();
    expect(VisitLog::find($oldVisit->id))->toBeNull();
    expect(VisitLog::find($recentVisit->id))->not->toBeNull();
});
