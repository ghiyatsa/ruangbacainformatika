<?php

use App\Models\Setting;
use App\Services\KioskApiKeyManager;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\artisan;
use function Pest\Laravel\getJson;

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

it('reports the unconfigured state through the artisan command', function () {
    artisan('kiosk:api-key show')
        ->expectsOutputToContain('belum dibuat')
        ->assertSuccessful();
});

it('generates a key through the artisan command', function () {
    artisan('kiosk:api-key generate --force')
        ->assertSuccessful();

    expect(app(KioskApiKeyManager::class)->isConfigured())->toBeTrue();
});

it('revokes the key through the artisan command', function () {
    app(KioskApiKeyManager::class)->generate();

    artisan('kiosk:api-key revoke --force')
        ->assertSuccessful();

    expect(app(KioskApiKeyManager::class)->isConfigured())->toBeFalse();
});

it('rejects an unknown artisan action', function () {
    artisan('kiosk:api-key nonsense')
        ->assertFailed();
});

it('produces a key that authenticates a real kiosk api request', function () {
    $plainKey = app(KioskApiKeyManager::class)->generate();

    getJson(route('api.kiosk.bootstrap'), ['X-Kiosk-Api-Key' => $plainKey])
        ->assertSuccessful();

    getJson(route('api.kiosk.bootstrap'), ['X-Kiosk-Api-Key' => 'rbk_salah'])
        ->assertStatus(401);
});
