<?php

use App\Models\KioskDevice;
use App\Models\MemberRegistrationClaim;
use App\Models\Setting;
use App\Models\User;
use App\Models\VisitLog;
use App\Services\KioskPinManager;
use App\Support\KioskIdlePolicy;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\call;
use function Pest\Laravel\get;
use function Pest\Laravel\instance;
use function Pest\Laravel\post;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
    Carbon::setTestNow('2026-06-07 03:00:00');

    Setting::query()->create([
        'section' => 'kiosk',
        'key' => 'pin_hash',
        'value' => Hash::make('123456'),
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
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
                ->where('kioskSession.operatingOpenTime', '08:00')
                ->where('kioskSession.operatingCloseTime', '17:00')
                ->has('kioskSession.withinOperatingHours')
                ->has('kioskSession.persistentForDevelopment')
                ->has('kioskSession.sessionExpiresAtIso')
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

it('kiosk routes apply the expected rate limiters', function () {
    $routes = app('router')->getRoutes();

    expect($routes->getByName('kiosk.pin.store')?->gatherMiddleware())
        ->toContain('throttle:kiosk-pin')
        ->and($routes->getByName('kiosk.books.search')?->gatherMiddleware())
        ->toContain('throttle:kiosk-book-search')
        ->and($routes->getByName('kiosk.members.find')?->gatherMiddleware())
        ->toContain('throttle:kiosk-member-lookup')
        ->and($routes->getByName('kiosk.members.status')?->gatherMiddleware())
        ->toContain('throttle:kiosk-member-status')
        ->and($routes->getByName('kiosk.visits.store')?->gatherMiddleware())
        ->toContain('throttle:kiosk-submit')
        ->and($routes->getByName('kiosk.loan-drafts.consume')?->gatherMiddleware())
        ->toContain('throttle:kiosk-consume');
});

it('kiosk rate limiters are registered with lobby-safe thresholds', function () {
    $request = Request::create(route('kiosk.index', absolute: false), 'GET', server: [
        'REMOTE_ADDR' => '127.0.0.1',
    ]);
    $request->setLaravelSession(app('session.store'));

    $rateLimiter = app(RateLimiter::class);
    $pinLimiter = $rateLimiter->limiter('kiosk-pin');
    $bookSearchLimiter = $rateLimiter->limiter('kiosk-book-search');
    $memberStatusLimiter = $rateLimiter->limiter('kiosk-member-status');
    $consumeLimiter = $rateLimiter->limiter('kiosk-consume');

    expect($pinLimiter)->not->toBeNull()
        ->and($bookSearchLimiter)->not->toBeNull()
        ->and($memberStatusLimiter)->not->toBeNull()
        ->and($consumeLimiter)->not->toBeNull();

    $pinLimit = $pinLimiter($request);
    $bookSearchLimit = $bookSearchLimiter($request);
    $memberStatusLimit = $memberStatusLimiter($request);
    $consumeLimit = $consumeLimiter($request);

    expect($pinLimit->maxAttempts)->toBe(8)
        ->and($pinLimit->decaySeconds)->toBe(60)
        ->and($bookSearchLimit->maxAttempts)->toBe(180)
        ->and($memberStatusLimit->maxAttempts)->toBe(180)
        ->and($consumeLimit->maxAttempts)->toBe(20);
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
    $mock->shouldReceive('sessionConfiguration')->andReturn([
        'timezone' => 'Asia/Jakarta',
        'operatingOpenTime' => '08:00',
        'operatingCloseTime' => '17:00',
        'withinOperatingHours' => true,
        'persistentForDevelopment' => false,
        'sessionExpiresAtIso' => now()->setTime(17, 0)->toIso8601String(),
    ]);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'ready')
                ->where('activeMenu', 'visit')
                ->where('kioskSession.withinOperatingHours', true),
        );
});

it('kiosk keeps verified sessions active during operating hours without idle expiry', function () {
    Carbon::setTestNow('2026-06-07 03:00:00');

    Setting::query()->updateOrCreate(
        ['section' => 'kiosk', 'key' => 'operating_open_time'],
        ['value' => '08:00'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'kiosk', 'key' => 'operating_close_time'],
        ['value' => '17:00'],
    );

    post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])->assertRedirect(route('kiosk.index'));

    KioskDevice::query()->update([
        'last_active_at' => Carbon::parse('2026-06-07 01:01:00'),
    ]);

    expect(app(KioskIdlePolicy::class)->isSessionStillActive(Carbon::parse('2026-06-07 01:01:00')))
        ->toBeTrue();
});

it('kiosk expires verified sessions at the operating close time', function () {
    Carbon::setTestNow('2026-06-07 09:30:00');

    Setting::query()->updateOrCreate(
        ['section' => 'kiosk', 'key' => 'operating_open_time'],
        ['value' => '08:00'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'kiosk', 'key' => 'operating_close_time'],
        ['value' => '17:00'],
    );

    post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])->assertRedirect(route('kiosk.index'));

    Carbon::setTestNow('2026-06-07 10:00:01');

    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'pin')
                ->where('kioskSession.withinOperatingHours', false),
        );

    assertDatabaseCount('kiosk_devices', 0);
});

it('kiosk cannot start a session outside operating hours', function () {
    Carbon::setTestNow('2026-06-07 13:30:00');

    Setting::query()->updateOrCreate(
        ['section' => 'kiosk', 'key' => 'operating_open_time'],
        ['value' => '08:00'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'kiosk', 'key' => 'operating_close_time'],
        ['value' => '17:00'],
    );

    post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])->assertSessionHasErrors([
        'pin' => 'Sesi kiosk hanya dapat dimulai pada jam operasional perpustakaan.',
    ]);
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

it('kiosk member registration accepts eligible unimal staff email domains', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.members.store'), [
        'name' => 'Dosen Unimal',
        'email' => 'dosen@unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus Bukit Indah',
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'member']))
        ->assertSessionHasNoErrors();

    assertDatabaseHas('member_registration_claims', [
        'name' => 'Dosen Unimal',
        'email' => 'dosen@unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus Bukit Indah',
        'status' => MemberRegistrationClaim::STATUS_PENDING,
    ]);
});

it('kiosk member registration rejects email domains outside unimal', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.members.store'), [
        'name' => 'Member Kiosk',
        'email' => 'member@example.com',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
    ])->assertSessionHasErrors([
        'email' => 'Gunakan email UNIMAL dengan domain @mhs.unimal.ac.id atau @unimal.ac.id.',
    ]);
});

it('kiosk member registration rejects duplicate email or whatsapp number', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    User::factory()->create([
        'email' => 'existing@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
    ]);

    post(route('kiosk.members.store'), [
        'name' => 'Duplicated Kiosk Member',
        'email' => 'existing@mhs.unimal.ac.id',
        'whatsapp' => '08999999999', // Different phone to isolate email error, or same to test both
        'address' => 'Jl. Kampus No. 1',
    ])->assertSessionHasErrors(['email']);

    post(route('kiosk.members.store'), [
        'name' => 'Duplicated Kiosk Member',
        'email' => 'other@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
    ])->assertSessionHasErrors(['whatsapp']);
});

it('kiosk member registration rejects duplicate whatsapp numbers with alternate formatting', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    User::factory()->create([
        'email' => 'existing@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
    ]);

    post(route('kiosk.members.store'), [
        'name' => 'Duplicated Kiosk Member',
        'email' => 'other@mhs.unimal.ac.id',
        'whatsapp' => '+62 812-3456-789',
        'address' => 'Jl. Kampus No. 1',
    ])->assertSessionHasErrors(['whatsapp']);
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

it('kiosk lock endpoint clears the active kiosk session', function () {
    post(route('kiosk.pin.store'), [
        'pin' => '123456',
    ])->assertRedirect(route('kiosk.index'));

    assertDatabaseCount('kiosk_devices', 1);

    post(route('kiosk.lock'))
        ->assertSuccessful()
        ->assertJsonPath('locked', true);

    assertDatabaseCount('kiosk_devices', 0);
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

it('kiosk visit stores the active kiosk device when available', function () {
    $device = KioskDevice::query()->create([
        'session_id' => session()->getId(),
        'device_token' => 'visit-device-token',
        'ip_address' => '127.0.0.1',
        'network_scope' => '127.0.0.0/24',
        'last_active_at' => now(),
    ]);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldReceive('currentDevice')->andReturn($device);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.visits.store'), [
        'name' => 'Pengunjung Perangkat',
        'visitor_type' => VisitLog::VISITOR_TYPE_STAFF,
        'purpose' => 'administration',
        'notes' => 'Mencatat kunjungan dari perangkat kiosk aktif.',
    ])->assertRedirect(route('kiosk.index', ['menu' => 'visit']));

    assertDatabaseHas('visit_logs', [
        'name' => 'Pengunjung Perangkat',
        'kiosk_device_id' => $device->id,
    ]);
});

it('kiosk stores public visitor institution and phone details', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.visits.store'), [
        'name' => 'Tamu Instansi',
        'visitor_type' => VisitLog::VISITOR_TYPE_UMUM,
        'institution' => 'Dinas Arsip Daerah',
        'phone' => '081234567890',
        'purpose' => 'reference',
        'notes' => 'Koordinasi referensi arsip daerah.',
    ])
        ->assertRedirect(route('kiosk.index', ['menu' => 'visit']))
        ->assertSessionHasNoErrors();

    assertDatabaseHas('visit_logs', [
        'name' => 'Tamu Instansi',
        'visitor_type' => VisitLog::VISITOR_TYPE_UMUM,
        'institution' => 'Dinas Arsip Daerah',
        'phone' => '081234567890',
        'purpose' => 'reference',
        'notes' => 'Koordinasi referensi arsip daerah.',
    ]);
});

it('kiosk find member returns member details when found', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john.doe@mhs.unimal.ac.id',
        'whatsapp' => '081234567890',
    ]);

    get(route('kiosk.members.find', ['identifier' => 'john.doe@mhs.unimal.ac.id']))
        ->assertSuccessful()
        ->assertJson([
            'member' => [
                'name' => 'John Doe',
                'emailMasked' => 'john.doe@mhs.unimal.ac.id',
                'whatsappMasked' => '0812******90',
            ],
        ]);
});

it('kiosk find member returns null when not found', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    get(route('kiosk.members.find', ['identifier' => 'unknown@example.com']))
        ->assertSuccessful()
        ->assertJson([
            'member' => null,
        ]);
});
