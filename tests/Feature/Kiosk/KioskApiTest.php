<?php

use App\Models\KioskDevice;
use App\Models\Setting;
use App\Models\User;
use App\Models\VisitLog;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

/**
 * Uji endpoint API kiosk untuk klien non-browser (aplikasi Flutter).
 *
 * Autentikasi memakai header X-Kiosk-Device-Token, bukan session/cookie.
 */
beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
    Carbon::setTestNow('2026-06-07 03:00:00'); // 10:00 WIB — dalam jam operasional

    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'pin_hash',
        'value' => Hash::make('123456'),
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * Helper: aktivasi perangkat dan kembalikan tokennya.
 */
function activateKioskDevice(string $pin = '123456', string $name = 'Kiosk Uji'): string
{
    $response = postJson(route('api.kiosk.devices.activate'), [
        'pin' => $pin,
        'device_name' => $name,
    ])->assertSuccessful();

    return (string) $response->json('device_token');
}

// ---------------------------------------------------------------------------
// Aktivasi perangkat
// ---------------------------------------------------------------------------

it('activates a device with a valid pin and returns a device token', function () {
    postJson(route('api.kiosk.devices.activate'), [
        'pin' => '123456',
        'device_name' => 'Kiosk Perpustakaan Lt.1',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['device_token', 'expires_at', 'session'])
        ->assertJsonPath('session.timezone', 'Asia/Jakarta');

    $this->assertDatabaseHas('kiosk_devices', [
        'name' => 'Kiosk Perpustakaan Lt.1',
    ]);
});

it('rejects device activation with an incorrect pin', function () {
    postJson(route('api.kiosk.devices.activate'), ['pin' => '000000'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('pin');
});

it('rejects device activation when the pin is not configured', function () {
    Setting::query()->where('section', 'kiosk')->where('key', 'pin_hash')->delete();

    postJson(route('api.kiosk.devices.activate'), ['pin' => '123456'])
        ->assertStatus(503);
});

it('rejects device activation outside operating hours', function () {
    Carbon::setTestNow('2026-06-07 18:00:00'); // 01:00 WIB — di luar 08:00-17:00

    postJson(route('api.kiosk.devices.activate'), ['pin' => '123456'])
        ->assertStatus(403);
});

// ---------------------------------------------------------------------------
// Autentikasi device token
// ---------------------------------------------------------------------------

it('rejects requests without a device token', function () {
    getJson(route('api.kiosk.bootstrap'))
        ->assertStatus(401)
        ->assertJsonPath('message', 'Perangkat kiosk tidak terautentikasi.');
});

it('rejects requests with an unknown device token', function () {
    getJson(route('api.kiosk.bootstrap'), [
        'X-Kiosk-Device-Token' => str_repeat('x', 64),
    ])->assertStatus(401);
});

it('rejects an expired device token and removes the device', function () {
    $device = KioskDevice::query()->create([
        'session_id' => 'api:expired',
        'device_token' => str_repeat('e', 64),
        'last_active_at' => now()->subHours(25),
    ]);

    getJson(route('api.kiosk.bootstrap'), [
        'X-Kiosk-Device-Token' => $device->device_token,
    ])->assertStatus(401);

    $this->assertDatabaseMissing('kiosk_devices', ['id' => $device->getKey()]);
});

it('accepts a valid device token', function () {
    $token = activateKioskDevice();

    getJson(route('api.kiosk.bootstrap'), ['X-Kiosk-Device-Token' => $token])
        ->assertSuccessful()
        ->assertJsonStructure([
            'loan_max_books',
            'visitor_type_options',
            'purpose_options',
            'session',
        ]);
});

it('invalidates the device token on lock', function () {
    $token = activateKioskDevice();

    postJson(route('api.kiosk.lock'), [], ['X-Kiosk-Device-Token' => $token])
        ->assertSuccessful()
        ->assertJsonPath('locked', true);

    getJson(route('api.kiosk.bootstrap'), ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(401);
});

// ---------------------------------------------------------------------------
// Kunjungan
// ---------------------------------------------------------------------------

it('records a visit from an authenticated device', function () {
    $token = activateKioskDevice();

    postJson(route('api.kiosk.visits.store'), [
        'name' => 'Ahmad Fauzi',
        'visitor_type' => VisitLog::VISITOR_TYPE_UMUM,
        'purpose' => 'read',
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(201)
        ->assertJsonPath('visit.name', 'Ahmad Fauzi');

    $this->assertDatabaseHas('visit_logs', [
        'name' => 'Ahmad Fauzi',
        'kiosk_device_id' => KioskDevice::query()->latest('id')->first()->getKey(),
    ]);
});

// ---------------------------------------------------------------------------
// Pencarian buku
// ---------------------------------------------------------------------------

it('requires a device token to search books', function () {
    getJson(route('api.kiosk.books.search', ['q' => 'algoritma', 'mode' => 'borrow']))
        ->assertStatus(401);
});

it('returns an empty book list for borrow mode without a keyword', function () {
    $token = activateKioskDevice();

    getJson(route('api.kiosk.books.search', ['mode' => 'borrow']), ['X-Kiosk-Device-Token' => $token])
        ->assertSuccessful()
        ->assertJsonPath('books', []);
});

// ---------------------------------------------------------------------------
// Pencarian anggota — tidak boleh membocorkan email penuh
// ---------------------------------------------------------------------------

it('never exposes the full email when looking up a member', function () {
    $token = activateKioskDevice();

    $user = User::factory()->create([
        'email' => 'said.230170162@mhs.unimal.ac.id',
        'whatsapp' => '085712345675',
    ]);

    $response = getJson(
        route('api.kiosk.members.find', ['identifier' => '230170162']),
        ['X-Kiosk-Device-Token' => $token],
    )->assertSuccessful();

    $member = $response->json('member');

    expect($member)->toHaveKeys(['name', 'hasEmail', 'emailDomain', 'whatsappMasked'])
        ->and($member)->not->toHaveKey('emailMasked')
        ->and($member)->not->toHaveKey('email')
        ->and($member['emailDomain'])->toBe('mhs.unimal.ac.id');

    // Email penuh tidak boleh muncul di mana pun pada respons.
    expect(json_encode($response->json()))->not->toContain('said.230170162@mhs.unimal.ac.id');
});

// ---------------------------------------------------------------------------
// Registrasi anggota — state via kiosk_device_id, bukan session
// ---------------------------------------------------------------------------

it('creates a member registration claim tied to the device', function () {
    $token = activateKioskDevice();
    $device = KioskDevice::query()->latest('id')->first();

    postJson(route('api.kiosk.members.store'), [
        'name' => 'Siti Aminah',
        'email' => 'siti@mhs.unimal.ac.id',
        'whatsapp' => '081234567890',
        'address' => 'Jl. Cot Tengku Nie, Lhokseumawe',
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(201)
        ->assertJsonStructure(['claim' => ['id', 'linkUrl', 'qrSvg', 'status']]);

    $this->assertDatabaseHas('member_registration_claims', [
        'name' => 'Siti Aminah',
        'kiosk_device_id' => $device->getKey(),
    ]);
});

it('reports the registration status for the current device', function () {
    $token = activateKioskDevice();

    postJson(route('api.kiosk.members.store'), [
        'name' => 'Siti Aminah',
        'email' => 'siti@mhs.unimal.ac.id',
        'whatsapp' => '081234567890',
        'address' => 'Jl. Cot Tengku Nie, Lhokseumawe',
    ], ['X-Kiosk-Device-Token' => $token])->assertStatus(201);

    getJson(route('api.kiosk.members.status'), ['X-Kiosk-Device-Token' => $token])
        ->assertSuccessful()
        ->assertJsonPath('claim.status', 'pending');
});

it('cancels the pending registration claim for the current device', function () {
    $token = activateKioskDevice();

    postJson(route('api.kiosk.members.store'), [
        'name' => 'Siti Aminah',
        'email' => 'siti@mhs.unimal.ac.id',
        'whatsapp' => '081234567890',
        'address' => 'Jl. Cot Tengku Nie, Lhokseumawe',
    ], ['X-Kiosk-Device-Token' => $token])->assertStatus(201);

    postJson(route('api.kiosk.members.cancel'), [], ['X-Kiosk-Device-Token' => $token])
        ->assertSuccessful()
        ->assertJsonPath('cancelled', true);

    $this->assertDatabaseHas('member_registration_claims', [
        'name' => 'Siti Aminah',
        'status' => 'expired',
    ]);
});
