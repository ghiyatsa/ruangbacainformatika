<?php

use App\Filament\Clusters\Settings\Pages\IntegrationSettings;
use App\Models\Setting;
use App\Models\User;
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

test('integration settings can persist similarity weights and warn for full resync', function () {
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
        ->assertNotified('Pengaturan integrasi disimpan');

    expect(Setting::query()->where('section', 'integration')->where('key', 'similarity_weight_judul')->value('value'))->toBe('0.6')
        ->and(Setting::query()->where('section', 'integration')->where('key', 'similarity_weight_abstrak')->value('value'))->toBe('0.25')
        ->and(Setting::query()->where('section', 'integration')->where('key', 'similarity_weight_kata_kunci')->value('value'))->toBe('0.15');
});

test('integration settings page shows the full skripsi resync action', function () {
    $user = makeIntegrationSuperAdmin();

    actingAs($user);

    Livewire::test(IntegrationSettings::class)
        ->assertSee('Sync Ulang Semua Skripsi');
});
