<?php

use App\Models\KioskDevice;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

const KIOSK_TEST_API_KEY = 'rbk_test_key_untuk_pengujian_otomatis_1234567890';

/**
 * Uji autentikasi API key bersama untuk aplikasi kiosk (Flutter).
 *
 * API key menggantikan PIN pada sisi klien: teknisi memasukkan key sekali ke
 * konfigurasi aplikasi, setelah itu tidak ada layar PIN lagi. Key berlaku
 * permanen sampai dirotasi/dicabut dari server.
 */
beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
    Carbon::setTestNow('2026-06-07 03:00:00');

    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'api_key_hash',
        'value' => Hash::make(KIOSK_TEST_API_KEY),
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function kioskApiHeaders(): array
{
    return ['X-Kiosk-Api-Key' => KIOSK_TEST_API_KEY];
}

it('accepts a valid api key without any pin', function () {
    getJson(route('api.kiosk.bootstrap'), kioskApiHeaders())
        ->assertSuccessful()
        ->assertJsonStructure(['loan_max_books', 'visitor_type_options', 'session']);
});

it('rejects a request without an api key', function () {
    getJson(route('api.kiosk.bootstrap'))
        ->assertStatus(401)
        ->assertJsonPath('message', 'Perangkat kiosk tidak terautentikasi.');
});

it('rejects an incorrect api key', function () {
    getJson(route('api.kiosk.bootstrap'), ['X-Kiosk-Api-Key' => 'rbk_salah'])
        ->assertStatus(401);
});

it('rejects an api key when none is configured', function () {
    Setting::query()->where('section', 'kiosk')->where('key', 'api_key_hash')->delete();

    getJson(route('api.kiosk.bootstrap'), kioskApiHeaders())
        ->assertStatus(401);
});

it('allows all kiosk endpoints with a valid api key', function () {
    getJson(route('api.kiosk.books.search', ['q' => 'algoritma', 'mode' => 'borrow']), kioskApiHeaders())
        ->assertSuccessful();

    getJson(route('api.kiosk.members.status'), kioskApiHeaders())
        ->assertSuccessful();

    postJson(route('api.kiosk.lock'), [], kioskApiHeaders())
        ->assertSuccessful();
});

it('never exposes the full email when using an api key', function () {
    $user = User::factory()->create([
        'email' => 'said.230170162@mhs.unimal.ac.id',
        'whatsapp' => '085712345675',
    ]);

    $response = getJson(
        route('api.kiosk.members.find', ['identifier' => '230170162']),
        kioskApiHeaders(),
    )->assertSuccessful();

    expect(json_encode($response->json()))->not->toContain('said.230170162@mhs.unimal.ac.id');
});

it('stops accepting requests immediately after the api key is revoked', function () {
    getJson(route('api.kiosk.bootstrap'), kioskApiHeaders())->assertSuccessful();

    Setting::query()->where('section', 'kiosk')->where('key', 'api_key_hash')->delete();

    getJson(route('api.kiosk.bootstrap'), kioskApiHeaders())->assertStatus(401);
});

it('still accepts a valid device token alongside the api key', function () {
    $device = KioskDevice::query()->create([
        'session_id' => 'api:mixed',
        'device_token' => str_repeat('m', 64),
        'last_active_at' => now(),
    ]);

    getJson(route('api.kiosk.bootstrap'), ['X-Kiosk-Device-Token' => $device->device_token])
        ->assertSuccessful();
});
