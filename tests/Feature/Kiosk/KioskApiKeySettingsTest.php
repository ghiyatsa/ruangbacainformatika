<?php

use App\Filament\Clusters\Settings\Pages\KioskApiKeySettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\KioskApiKeyManager;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

function makeKioskSettingsAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

function makeKioskSettingsMember(): User
{
    return User::factory()->create();
}

beforeEach(function () {
    Setting::query()->where('section', KioskApiKeyManager::SECTION)->delete();
});

it('generates a key whose hash verifies against the stored value', function () {
    $manager = app(KioskApiKeyManager::class);

    expect($manager->isConfigured())->toBeFalse();

    $plainKey = $manager->generate();

    expect($plainKey)->toStartWith(KioskApiKeyManager::KEY_PREFIX)
        ->and($manager->isConfigured())->toBeTrue()
        ->and($manager->createdAt())->not->toBeNull();

    $storedHash = Setting::query()
        ->where('section', KioskApiKeyManager::SECTION)
        ->where('key', KioskApiKeyManager::HASH_KEY)
        ->value('value');

    // Middleware memverifikasi dengan Hash::check, jadi hash harus bcrypt.
    expect(Hash::check($plainKey, $storedHash))->toBeTrue();
});

it('never stores the plaintext key', function () {
    $plainKey = app(KioskApiKeyManager::class)->generate();

    $storedHash = Setting::query()
        ->where('section', KioskApiKeyManager::SECTION)
        ->where('key', KioskApiKeyManager::HASH_KEY)
        ->value('value');

    expect($storedHash)->not->toBe($plainKey);
});

it('invalidates the previous key when rotating', function () {
    $manager = app(KioskApiKeyManager::class);

    $firstKey = $manager->generate();
    $secondKey = $manager->generate();

    $storedHash = Setting::query()
        ->where('section', KioskApiKeyManager::SECTION)
        ->where('key', KioskApiKeyManager::HASH_KEY)
        ->value('value');

    expect(Hash::check($secondKey, $storedHash))->toBeTrue()
        ->and(Hash::check($firstKey, $storedHash))->toBeFalse();
});

it('does not leak the key through the masked preview', function () {
    $manager = app(KioskApiKeyManager::class);
    $plainKey = $manager->generate();

    $preview = $manager->maskedPreview();

    expect($preview)->not->toBeNull()
        ->and($preview)->toStartWith(KioskApiKeyManager::KEY_PREFIX)
        ->and($preview)->not->toContain($plainKey);
});

it('revokes the key and clears its metadata', function () {
    $manager = app(KioskApiKeyManager::class);
    $manager->generate();

    $manager->revoke();

    expect($manager->isConfigured())->toBeFalse()
        ->and($manager->createdAt())->toBeNull()
        ->and($manager->maskedPreview())->toBeNull();
});

it('allows administrators to open the kiosk api key settings page', function () {
    actingAs(makeKioskSettingsAdmin());

    expect(KioskApiKeySettings::canAccess())->toBeTrue();

    Livewire::test(KioskApiKeySettings::class)
        ->assertSuccessful();
});

it('denies regular members access to the kiosk api key settings page', function () {
    actingAs(makeKioskSettingsMember());

    expect(KioskApiKeySettings::canAccess())->toBeFalse();
});

it('generates a usable key through the admin panel action', function () {
    actingAs(makeKioskSettingsAdmin());

    Livewire::test(KioskApiKeySettings::class)
        ->callAction('generate')
        ->assertNotified('API key kiosk berhasil dibuat');

    expect(app(KioskApiKeyManager::class)->isConfigured())->toBeTrue();
});

it('revokes the key through the admin panel action', function () {
    app(KioskApiKeyManager::class)->generate();

    actingAs(makeKioskSettingsAdmin());

    Livewire::test(KioskApiKeySettings::class)
        ->callAction('revoke')
        ->assertNotified('API key kiosk dicabut');

    expect(app(KioskApiKeyManager::class)->isConfigured())->toBeFalse();
});

it('produces a key that authenticates a real kiosk api request', function () {
    $plainKey = app(KioskApiKeyManager::class)->generate();

    getJson(route('api.kiosk.bootstrap'), ['X-Kiosk-Api-Key' => $plainKey])
        ->assertSuccessful();

    getJson(route('api.kiosk.bootstrap'), ['X-Kiosk-Api-Key' => 'rbk_salah'])
        ->assertStatus(401);
});
