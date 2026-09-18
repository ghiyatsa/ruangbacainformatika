<?php

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
 * Uji validasi registrasi anggota & buku tamu lewat API kiosk (klien Flutter).
 *
 * Cakupan ini dipindahkan dari `tests/Feature/Kiosk/KioskAccessTest.php` yang
 * menguji route web `/kiosk/*`. Route web sudah dihapus, tetapi
 * `RegisterMemberRequest` / `SubmitVisitRequest` dan service-nya tetap hidup
 * dan dipakai aplikasi desktop — pengamannya harus tetap ada.
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
 * Helper: aktifkan perangkat kiosk dan kembalikan tokennya.
 */
function validationApiDeviceToken(): string
{
    $response = postJson(route('api.kiosk.devices.activate'), [
        'pin' => '123456',
        'device_name' => 'Kiosk Validasi Uji',
    ])->assertSuccessful();

    return (string) $response->json('device_token');
}

/**
 * Payload registrasi anggota yang valid; timpa sebagian sesuai kebutuhan uji.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function memberRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus Bukit Indah',
    ], $overrides);
}

// ---------------------------------------------------------------------------
// Registrasi anggota — validasi
// ---------------------------------------------------------------------------

it('requires the address field for borrowing readiness', function () {
    $token = validationApiDeviceToken();

    $payload = memberRegistrationPayload();
    unset($payload['address']);

    postJson(route('api.kiosk.members.store'), $payload, ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors('address');
});

it('rejects an invalid whatsapp number and an unclear address', function () {
    $token = validationApiDeviceToken();

    postJson(route('api.kiosk.members.store'), memberRegistrationPayload([
        'whatsapp' => '12345',
        'address' => '???',
    ]), ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['whatsapp', 'address']);
});

it('accepts an eligible unimal staff email domain', function () {
    $token = validationApiDeviceToken();

    postJson(route('api.kiosk.members.store'), memberRegistrationPayload([
        'name' => 'Dosen Unimal',
        'email' => 'dosen@unimal.ac.id',
    ]), ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(201)
        ->assertJsonPath('claim.status', 'pending');

    $this->assertDatabaseHas('member_registration_claims', [
        'name' => 'Dosen Unimal',
        'email' => 'dosen@unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus Bukit Indah',
        'status' => 'pending',
    ]);
});

it('rejects email domains outside unimal', function () {
    $token = validationApiDeviceToken();

    postJson(route('api.kiosk.members.store'), memberRegistrationPayload([
        'email' => 'member@example.com',
    ]), ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'email' => 'Gunakan email UNIMAL dengan domain @mhs.unimal.ac.id atau @unimal.ac.id.',
        ]);
});

it('rejects a duplicate email or whatsapp number', function () {
    $token = validationApiDeviceToken();

    User::factory()->create([
        'email' => 'existing@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
    ]);

    postJson(route('api.kiosk.members.store'), memberRegistrationPayload([
        'email' => 'existing@mhs.unimal.ac.id',
        'whatsapp' => '08999999999',
    ]), ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');

    postJson(route('api.kiosk.members.store'), memberRegistrationPayload([
        'email' => 'other@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
    ]), ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors('whatsapp');
});

it('rejects duplicate whatsapp numbers written with alternate formatting', function () {
    $token = validationApiDeviceToken();

    User::factory()->create([
        'email' => 'existing@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
    ]);

    postJson(route('api.kiosk.members.store'), memberRegistrationPayload([
        'email' => 'other@mhs.unimal.ac.id',
        'whatsapp' => '+62 812-3456-789',
    ]), ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors('whatsapp');
});

// ---------------------------------------------------------------------------
// Buku tamu — validasi
// ---------------------------------------------------------------------------

it('rejects malformed phone and identity details in a visit', function () {
    $token = validationApiDeviceToken();

    postJson(route('api.kiosk.visits.store'), [
        'name' => 'Pengunjung Demo',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'identity_number' => 'ID-ABC',
        'phone' => '12345',
        'purpose' => 'read',
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['identity_number', 'phone']);
});

it('stores public visitor institution and phone details', function () {
    $token = validationApiDeviceToken();

    postJson(route('api.kiosk.visits.store'), [
        'name' => 'Tamu Instansi',
        'visitor_type' => VisitLog::VISITOR_TYPE_UMUM,
        'institution' => 'Dinas Arsip Daerah',
        'phone' => '081234567890',
        'purpose' => 'reference',
        'notes' => 'Koordinasi referensi arsip daerah.',
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertStatus(201)
        ->assertJsonPath('visit.name', 'Tamu Instansi');

    $this->assertDatabaseHas('visit_logs', [
        'name' => 'Tamu Instansi',
        'visitor_type' => VisitLog::VISITOR_TYPE_UMUM,
        'institution' => 'Dinas Arsip Daerah',
        'phone' => '081234567890',
        'purpose' => 'reference',
        'notes' => 'Koordinasi referensi arsip daerah.',
    ]);
});

// ---------------------------------------------------------------------------
// Pencarian anggota
// ---------------------------------------------------------------------------

it('returns member details when the member is found', function () {
    $token = validationApiDeviceToken();

    User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john.doe@mhs.unimal.ac.id',
        'whatsapp' => '081234567890',
    ]);

    getJson(route('api.kiosk.members.find', ['identifier' => 'john.doe@mhs.unimal.ac.id']), [
        'X-Kiosk-Device-Token' => $token,
    ])
        ->assertSuccessful()
        ->assertJsonPath('member.name', 'John Doe')
        ->assertJsonPath('member.emailDomain', 'mhs.unimal.ac.id');
});

it('returns null when the member is not found', function () {
    $token = validationApiDeviceToken();

    getJson(route('api.kiosk.members.find', ['identifier' => 'unknown@example.com']), [
        'X-Kiosk-Device-Token' => $token,
    ])
        ->assertSuccessful()
        ->assertJsonPath('member', null);
});
