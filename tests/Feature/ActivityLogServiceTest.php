<?php

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\actingAs;

it('records admin activity with actor and subject details', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create([
        'name' => 'Mahasiswa Audit',
        'email' => 'audit@example.test',
    ]);

    actingAs($admin);

    $log = app(ActivityLogService::class)->log(
        'users.approved',
        'Akun pengguna disetujui',
        $member,
        ['via' => 'manual'],
    );

    expect($log)->not->toBeNull()
        ->and($log?->user_id)->toBe($admin->getKey())
        ->and($log?->subject_type)->toBe($member->getMorphClass())
        ->and($log?->subject_id)->toBe($member->getKey())
        ->and($log?->subject_label)->toBe('Mahasiswa Audit')
        ->and($log?->properties)->toMatchArray(['via' => 'manual']);
});

it('redacts hidden values when logging setting changes', function () {
    $admin = User::factory()->create();

    actingAs($admin);

    app(ActivityLogService::class)->logSettingsUpdate(
        'integration',
        'Pengaturan integrasi',
        ['similarity_api_secret' => 'lama', 'turnstile_enabled' => false],
        ['similarity_api_secret' => 'baru', 'turnstile_enabled' => true],
        ['similarity_api_secret'],
    );

    $log = ActivityLog::query()->latest('id')->firstOrFail();

    expect($log->action)->toBe('settings.integration.updated')
        ->and($log->properties['changes']['similarity_api_secret']['before'])->toBe('[REDACTED]')
        ->and($log->properties['changes']['similarity_api_secret']['after'])->toBe('[REDACTED]')
        ->and($log->properties['changes']['turnstile_enabled']['before'])->toBeFalse()
        ->and($log->properties['changes']['turnstile_enabled']['after'])->toBeTrue();
});

it('fails gracefully when activity log persistence throws an exception', function () {
    $admin = User::factory()->create();

    actingAs($admin);
    Log::spy();

    $service = new class extends ActivityLogService
    {
        protected function newActivityLog(array $attributes): ActivityLog
        {
            return new class($attributes) extends ActivityLog
            {
                public function save(array $options = []): bool
                {
                    throw new RuntimeException('Storage unavailable');
                }
            };
        }
    };

    $result = $service->log('settings.integration.updated', 'Pengaturan integrasi diperbarui');

    expect($result)->toBeNull();

    Log::shouldHaveReceived('warning')
        ->once();
});
