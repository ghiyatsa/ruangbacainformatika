<?php

use App\Filament\Clusters\Settings\Pages\IntegrationSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function makeIntegrationSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('integration settings can persist similarity weights and warn for full resync', function () {
    $user = makeIntegrationSuperAdmin();

    actingAs($user);

    Livewire::test(IntegrationSettings::class)
        ->fillForm([
            'turnstile_enabled' => false,
            'similarity_api_url' => 'https://similarity.test',
            'similarity_api_secret' => 'sync-secret-1234567890',
            'similarity_api_timeout' => 15,
            'similarity_api_top_k' => 7,
            'similarity_api_threshold' => 0.55,
            'similarity_weight_judul' => 0.6,
            'similarity_weight_abstrak' => 0.25,
            'similarity_weight_kata_kunci' => 0.15,
            'whatsapp_api_url' => '',
            'whatsapp_api_token' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified('Pengaturan integrasi disimpan');

    expect(Setting::query()->where('section', 'integration')->where('key', 'similarity_weight_judul')->value('value'))->toBe('0.6')
        ->and(Setting::query()->where('section', 'integration')->where('key', 'similarity_weight_abstrak')->value('value'))->toBe('0.25')
        ->and(Setting::query()->where('section', 'integration')->where('key', 'similarity_weight_kata_kunci')->value('value'))->toBe('0.15')
        ->and(decrypt(Setting::query()->where('section', 'integration')->where('key', 'similarity_api_secret')->value('value')))->toBe('sync-secret-1234567890');
});

it('integration settings encrypts the whatsapp token before persisting it', function () {
    $user = makeIntegrationSuperAdmin();

    actingAs($user);

    Livewire::test(IntegrationSettings::class)
        ->fillForm([
            'turnstile_enabled' => false,
            'similarity_api_url' => 'https://similarity.test',
            'similarity_api_secret' => 'sync-secret-1234567890',
            'similarity_api_timeout' => 15,
            'similarity_api_top_k' => 7,
            'similarity_api_threshold' => 0.55,
            'similarity_weight_judul' => 0.6,
            'similarity_weight_abstrak' => 0.25,
            'similarity_weight_kata_kunci' => 0.15,
            'whatsapp_api_url' => 'https://api.fonnte.com/send',
            'whatsapp_api_token' => 'encrypted-whatsapp-token',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(decrypt(Setting::query()->where('section', 'integration')->where('key', 'whatsapp_api_token')->value('value')))
        ->toBe('encrypted-whatsapp-token');
});

it('integration settings page shows the full skripsi resync action', function () {
    $user = makeIntegrationSuperAdmin();

    actingAs($user);

    Livewire::test(IntegrationSettings::class)
        ->assertSee('Sinkronkan Ulang Semua Skripsi');
});

it('integration settings page shows the similarity reconciliation action', function () {
    $user = makeIntegrationSuperAdmin();

    actingAs($user);

    Livewire::test(IntegrationSettings::class)
        ->assertSee('Samakan Status dari Index API');
});

it('integration settings clears cached turnstile status after saving', function () {
    $user = makeIntegrationSuperAdmin();

    actingAs($user);

    Cache::put('settings.integration.turnstile_enabled', true, now()->addMinutes(5));

    Livewire::test(IntegrationSettings::class)
        ->fillForm([
            'turnstile_enabled' => false,
            'similarity_api_url' => 'https://similarity.test',
            'similarity_api_secret' => 'sync-secret-1234567890',
            'similarity_api_timeout' => 15,
            'similarity_api_top_k' => 7,
            'similarity_api_threshold' => 0.55,
            'similarity_weight_judul' => 0.6,
            'similarity_weight_abstrak' => 0.25,
            'similarity_weight_kata_kunci' => 0.15,
            'whatsapp_api_url' => '',
            'whatsapp_api_token' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Cache::has('settings.integration.turnstile_enabled'))->toBeFalse();
});
