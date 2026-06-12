<?php

use App\Filament\Clusters\Settings\Pages\KioskSettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\KioskPinManager;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\instance;

function makeKioskSettingsSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('kiosk settings rotate active sessions when the pin is updated', function () {
    $user = makeKioskSettingsSuperAdmin();

    actingAs($user);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isConfigured')->andReturn(true);
    $mock->shouldReceive('rotateSessions')->once()->andReturn(2);
    instance(KioskPinManager::class, $mock);

    Livewire::test(KioskSettings::class)
        ->fillForm([
            'pin' => '654321',
            'allowed_networks' => "192.168.10.0/24\n10.10.0.0/16",
            'operating_open_time' => '08:00',
            'operating_close_time' => '17:00',
            'idle_timeout_open_minutes' => 20,
            'idle_timeout_closed_minutes' => 4,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified('Pengaturan kios disimpan');

    expect(Setting::query()->where('section', 'kiosk')->where('key', 'pin_hash')->exists())->toBeTrue()
        ->and(str_replace("\r\n", "\n", (string) Setting::query()->where('section', 'kiosk')->where('key', 'allowed_networks')->value('value')))
        ->toBe("192.168.10.0/24\n10.10.0.0/16")
        ->and((string) Setting::query()->where('section', 'kiosk')->where('key', 'operating_open_time')->value('value'))
        ->toBe('08:00')
        ->and((string) Setting::query()->where('section', 'kiosk')->where('key', 'operating_close_time')->value('value'))
        ->toBe('17:00')
        ->and((string) Setting::query()->where('section', 'kiosk')->where('key', 'idle_timeout_open_minutes')->value('value'))
        ->toBe('20')
        ->and((string) Setting::query()->where('section', 'kiosk')->where('key', 'idle_timeout_closed_minutes')->value('value'))
        ->toBe('4');
});
