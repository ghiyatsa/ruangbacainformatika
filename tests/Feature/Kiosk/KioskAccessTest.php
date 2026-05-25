<?php

use App\Models\KioskDevice;
use App\Models\MemberRegistrationClaim;
use App\Models\Setting;
use App\Models\VisitLog;
use App\Services\KioskPinManager;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\call;
use function Pest\Laravel\get;
use function Pest\Laravel\instance;
use function Pest\Laravel\post;

beforeEach(function () {
    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'pin_hash',
        'value' => Hash::make('123456'),
    ]);
});

it('kiosk denies access from networks outside the allowlist', function () {
    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'allowed_networks',
        'value' => '192.168.10.0/24',
    ]);

    call('GET', route('kiosk.index', absolute: false), [], [], [], [
        'REMOTE_ADDR' => '10.10.10.15',
    ])
        ->assertForbidden();
});

it('kiosk shows pin entry when not verified', function () {
    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'pin')
                ->where('pageTitle', 'Masuk Layanan Mandiri')
                ->where('pageSubtitle', 'Masukkan PIN untuk mulai.')
                ->has('visitorTypeOptions')
                ->has('purposeOptions'),
        );
});

it('kiosk device cookies are only trusted from the same network scope', function () {
    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'allowed_networks',
        'value' => '10.10.0.0/16',
    ]);

    KioskDevice::query()->create([
        'session_id' => 'old-session',
        'device_token' => 'trusted-device-token',
        'ip_address' => '10.10.10.20',
        'network_scope' => '10.10.10.0/24',
        'last_active_at' => now(),
    ]);

    call('GET', route('kiosk.index', absolute: false), [], [
        KioskPinManager::COOKIE_DEVICE_TOKEN_KEY => 'trusted-device-token',
    ], [], [
        'REMOTE_ADDR' => '10.10.20.9',
    ])
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'pin'),
        );
});

it('kiosk shows a neutral message when the pin is not configured', function () {
    Setting::query()->where('section', 'kiosk')->where('key', 'pin_hash')->delete();

    post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])
        ->assertSessionHasErrors([
            'pin' => 'PIN kiosk belum tersedia. Silakan hubungi petugas perpustakaan.',
        ]);
});

it('kiosk allows access after valid pin entry', function () {
    post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])
        ->assertRedirect(route('kiosk.index'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'PIN berhasil diverifikasi.');

    assertDatabaseHas('kiosk_devices', [
        'ip_address' => '127.0.0.1',
        'network_scope' => '127.0.0.0/24',
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'ready')
                ->where('activeMenu', 'visit')
                ->where('pageSubtitle', 'Pilih layanan yang ingin digunakan.'),
        );
});

it('kiosk respects the selected menu query when already verified', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    get(route('kiosk.index', ['menu' => 'borrow']))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'ready')
                ->where('activeMenu', 'borrow'),
        );
});

it('rotating kiosk sessions requires the pin again', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('rotateSessions')->once()->andReturn(2);
    $mock->shouldReceive('isVerified')->andReturn(false);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    app(KioskPinManager::class)->rotateSessions();

    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'pin'),
        );
});

it('rotating kiosk sessions clears kiosk devices without breaking visit log history', function () {
    $device = KioskDevice::query()->create([
        'session_id' => 'rotate-session',
        'device_token' => 'rotate-device-token',
        'ip_address' => '10.10.10.10',
        'network_scope' => '10.10.10.0/24',
        'last_active_at' => now(),
    ]);

    $visitLog = VisitLog::query()->create([
        'name' => 'Pengunjung Rotasi',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'identity_number' => '230170099',
        'purpose' => 'borrow_return',
        'kiosk_device_id' => $device->id,
        'visited_at' => now(),
    ]);

    $nextVersion = app(KioskPinManager::class)->rotateSessions();
    $visitLog->refresh();

    expect($nextVersion)->toBe(2)
        ->and($visitLog->kiosk_device_id)->toBeNull();

    assertDatabaseCount('kiosk_devices', 0);
});

it('kiosk member registration validates the required fields for borrowing readiness', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.members.store'), [
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
    ])->assertSessionHasErrors(['address']);
});

it('kiosk member registration rejects invalid whatsapp and unclear address', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.members.store'), [
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '12345',
        'address' => '???',
    ])->assertSessionHasErrors([
        'whatsapp',
        'address',
    ]);
});

it('kiosk visit rejects malformed phone and identity details', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.visits.store'), [
        'name' => 'Pengunjung Demo',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'identity_number' => 'ID-ABC',
        'phone' => '12345',
        'purpose' => 'read',
    ])->assertSessionHasErrors([
        'identity_number',
        'phone',
    ]);
});

it('kiosk member registration creates an account-link token for google onboarding', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $response = post(route('kiosk.members.store'), [
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
    ]);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))
        ->toBe(route('kiosk.index', ['menu' => 'member']));

    expect(session('inertia.flash_data.toast'))->toBe([
        'type' => 'success',
        'message' => 'QR siap digunakan. Scan dari ponsel untuk menautkan akun Google.',
    ]);

    assertDatabaseHas('member_registration_claims', [
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'status' => MemberRegistrationClaim::STATUS_PENDING,
    ]);
});

it('kiosk shows the latest live status for the active member registration claim', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $claim = MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'token_hash' => hash('sha256', 'rb-link-status'),
        'expires_at' => now()->addMinutes(30),
        'last_error_message' => 'Gunakan akun Google dengan email UNIMAL yang sama seperti data registrasi.',
        'last_error_at' => now(),
    ]);

    $presentedClaim = [
        'id' => $claim->id,
        'name' => $claim->name,
        'email' => $claim->email,
        'whatsapp' => $claim->whatsapp,
        'address' => $claim->address,
        'linkUrl' => 'https://ruangbaca.test/account-link/rb-link-status',
        'qrSvg' => '<svg></svg>',
        'status' => MemberRegistrationClaim::STATUS_PENDING,
        'expiresAt' => $claim->expires_at->toIso8601String(),
        'claimedAt' => null,
        'lastErrorMessage' => null,
        'lastErrorAt' => null,
        'approvalPending' => false,
    ];

    $this->withSession([
        'kiosk.member_registration_claim' => $presentedClaim,
    ]);

    get(route('kiosk.index', ['menu' => 'member']))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('memberRegistrationClaim.id', $claim->id)
                ->where('memberRegistrationClaim.status', MemberRegistrationClaim::STATUS_PENDING)
                ->where('memberRegistrationClaim.lastErrorMessage', 'Gunakan akun Google dengan email UNIMAL yang sama seperti data registrasi.')
                ->where('memberRegistrationClaim.approvalPending', false),
        );
});

it('kiosk member registration status endpoint returns the latest stored state', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $claim = MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'token_hash' => hash('sha256', 'rb-link-status'),
        'expires_at' => now()->addMinutes(30),
        'status' => MemberRegistrationClaim::STATUS_CLAIMED,
        'claimed_at' => now(),
    ]);

    $this->withSession([
        'kiosk.member_registration_claim' => [
            'id' => $claim->id,
            'name' => $claim->name,
            'email' => $claim->email,
            'whatsapp' => $claim->whatsapp,
            'address' => $claim->address,
            'linkUrl' => 'https://ruangbaca.test/account-link/rb-link-status',
            'qrSvg' => '<svg></svg>',
            'status' => MemberRegistrationClaim::STATUS_PENDING,
            'expiresAt' => $claim->expires_at->toIso8601String(),
            'claimedAt' => null,
            'lastErrorMessage' => null,
            'lastErrorAt' => null,
            'approvalPending' => false,
        ],
    ]);

    get(route('kiosk.members.status'))
        ->assertSuccessful()
        ->assertJsonPath('memberRegistrationClaim.id', $claim->id)
        ->assertJsonPath('memberRegistrationClaim.status', MemberRegistrationClaim::STATUS_CLAIMED);
});

it('kiosk member registration cancel endpoint expires pending registrations and clears the session', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $claim = MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'token_hash' => hash('sha256', 'rb-link-cancel'),
        'expires_at' => now()->addMinutes(3),
        'status' => MemberRegistrationClaim::STATUS_PENDING,
    ]);

    $this->withSession([
        'kiosk.member_registration_claim' => [
            'id' => $claim->id,
            'name' => $claim->name,
            'email' => $claim->email,
            'whatsapp' => $claim->whatsapp,
            'address' => $claim->address,
            'linkUrl' => 'https://ruangbaca.test/account-link/rb-link-cancel',
            'qrSvg' => '<svg></svg>',
            'status' => MemberRegistrationClaim::STATUS_PENDING,
            'expiresAt' => $claim->expires_at->toIso8601String(),
            'claimedAt' => null,
            'lastErrorMessage' => null,
            'lastErrorAt' => null,
            'approvalPending' => false,
        ],
    ]);

    post(route('kiosk.members.cancel'))
        ->assertSuccessful()
        ->assertJsonPath('cancelled', true)
        ->assertSessionMissing('kiosk.member_registration_claim');

    expect($claim->fresh()?->status)->toBe(MemberRegistrationClaim::STATUS_EXPIRED);
});

it('kiosk member registration cancel endpoint clears expired registrations from the session', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $claim = MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'token_hash' => hash('sha256', 'rb-link-expired'),
        'expires_at' => now()->subMinute(),
        'status' => MemberRegistrationClaim::STATUS_EXPIRED,
    ]);

    $this->withSession([
        'kiosk.member_registration_claim' => [
            'id' => $claim->id,
            'name' => $claim->name,
            'email' => $claim->email,
            'whatsapp' => $claim->whatsapp,
            'address' => $claim->address,
            'linkUrl' => 'https://ruangbaca.test/account-link/rb-link-expired',
            'qrSvg' => '<svg></svg>',
            'status' => MemberRegistrationClaim::STATUS_EXPIRED,
            'expiresAt' => $claim->expires_at->toIso8601String(),
            'claimedAt' => null,
            'lastErrorMessage' => null,
            'lastErrorAt' => null,
            'approvalPending' => false,
        ],
    ]);

    post(route('kiosk.members.cancel'))
        ->assertSuccessful()
        ->assertJsonPath('cancelled', true)
        ->assertSessionMissing('kiosk.member_registration_claim');
});

it('kiosk visit submission flashes a sonner toast after saving', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.visits.store'), [
        'name' => 'Pengunjung Kiosk',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'identity_number' => '230170020',
        'purpose' => 'read',
        'notes' => 'Membaca koleksi terbaru.',
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'visit']))
        ->assertSessionHas('inertia.flash_data.toast.message', 'Data kunjungan berhasil disimpan.');

    assertDatabaseHas('visit_logs', [
        'name' => 'Pengunjung Kiosk',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'identity_number' => '230170020',
        'purpose' => 'read',
    ]);
});
