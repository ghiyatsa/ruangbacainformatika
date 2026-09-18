<?php

use App\Models\Setting;
use App\Models\User;
use App\Models\VisitLog;
use App\Services\KioskBorrowVerificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

/**
 * Buku tamu cepat via Member Key (QR) — jalur tanpa isi form.
 *
 * Anggota memindai Member Key di menu Buku Tamu; identitas diambil dari
 * profil sehingga tidak ada kolom yang perlu diisi ulang.
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

function memberVisitDeviceToken(): string
{
    $response = postJson(route('api.kiosk.devices.activate'), [
        'pin' => '123456',
        'device_name' => 'Kiosk Buku Tamu',
    ])->assertSuccessful();

    return (string) $response->json('device_token');
}

/**
 * Anggota lengkap dengan Member Key aktif.
 *
 * @return array{0: User, 1: string}
 */
function memberWithKey(): array
{
    $user = User::factory()->create([
        'name' => 'Rahmat Hidayat',
        'email' => '210170123@mhs.unimal.ac.id',
        'whatsapp' => '081234567890',
        'whatsapp_verified_at' => now(),
    ]);

    $payload = app(KioskBorrowVerificationService::class)->generate($user)['payload'];

    return [$user, $payload];
}

it('records a visit from a scanned member key without any form field', function () {
    $token = memberVisitDeviceToken();
    [$user, $payload] = memberWithKey();

    postJson(route('api.kiosk.visits.member'), [
        'verification_payload' => $payload,
    ], ['X-Kiosk-Device-Token' => $token])
        ->assertSuccessful()
        ->assertJsonPath('visit.name', 'Rahmat Hidayat');

    $visit = VisitLog::query()->firstOrFail();

    expect($visit->name)->toBe('Rahmat Hidayat')
        ->and($visit->visitor_type)->toBe(VisitLog::VISITOR_TYPE_MAHASISWA)
        ->and($visit->identity_number)->toBe('210170123')
        ->and($visit->purpose)->toBe('read') // bawaan tanpa memilih
        ->and($visit->phone)->toBe('081234567890');
});

it('honours the chosen visit purpose', function () {
    $token = memberVisitDeviceToken();
    [, $payload] = memberWithKey();

    postJson(route('api.kiosk.visits.member'), [
        'verification_payload' => $payload,
        'purpose' => 'reference',
    ], ['X-Kiosk-Device-Token' => $token])->assertSuccessful();

    expect(VisitLog::query()->firstOrFail()->purpose)->toBe('reference');
});

it('consumes the member key so it cannot be reused', function () {
    $token = memberVisitDeviceToken();
    [, $payload] = memberWithKey();

    postJson(route('api.kiosk.visits.member'), [
        'verification_payload' => $payload,
    ], ['X-Kiosk-Device-Token' => $token])->assertSuccessful();

    postJson(route('api.kiosk.visits.member'), [
        'verification_payload' => $payload,
    ], ['X-Kiosk-Device-Token' => $token])->assertStatus(422);

    expect(VisitLog::query()->count())->toBe(1);
});

it('rejects a forged member key', function () {
    $token = memberVisitDeviceToken();

    postJson(route('api.kiosk.visits.member'), [
        'verification_payload' => 'MK-palsu-tidak-ada-di-cache',
    ], ['X-Kiosk-Device-Token' => $token])->assertStatus(422);

    expect(VisitLog::query()->count())->toBe(0);
});

it('requires a device token', function () {
    [, $payload] = memberWithKey();

    postJson(route('api.kiosk.visits.member'), [
        'verification_payload' => $payload,
    ])->assertStatus(401);

    expect(VisitLog::query()->count())->toBe(0);
});

it('rejects an unknown visit purpose', function () {
    $token = memberVisitDeviceToken();
    [, $payload] = memberWithKey();

    postJson(route('api.kiosk.visits.member'), [
        'verification_payload' => $payload,
        'purpose' => 'tujuan-ngawur',
    ], ['X-Kiosk-Device-Token' => $token])->assertStatus(422);
});
