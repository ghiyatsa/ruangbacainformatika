<?php

use App\Models\KioskDevice;
use App\Models\Setting;
use App\Models\User;
use App\Models\VisitLog;
use App\Services\KioskPinManager;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;
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

    $response = $this->call('GET', route('kiosk.index', absolute: false), [], [], [], [
        'REMOTE_ADDR' => '10.10.10.15',
    ]);

    $response->assertForbidden();
});

it('kiosk shows pin entry when not verified', function () {
    get(route('kiosk.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('kiosk/index')
                ->where('step', 'pin')
                ->where('pageSubtitle', 'Masukkan PIN perangkat untuk mengaktifkan kiosk ini.')
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

    $response = $this->call('GET', route('kiosk.index', absolute: false), [], [
        KioskPinManager::COOKIE_DEVICE_TOKEN_KEY => 'trusted-device-token',
    ], [], [
        'REMOTE_ADDR' => '10.10.20.9',
    ]);

    $response
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
                ->where('activeMenu', 'landing'),
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

it('kiosk member registration validates the required fields for borrowing readiness', function () {
    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.members.store'), [
        'name' => 'Member Kiosk',
        'email' => '230170020@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['address']);
});

it('kiosk member registration creates a usable member account', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $mock = mock(KioskPinManager::class);
    $mock->shouldReceive('isVerified')->andReturn(true);
    $mock->shouldIgnoreMissing();
    instance(KioskPinManager::class, $mock);

    post(route('kiosk.members.store'), [
        'name' => 'Member Kiosk',
        'email' => '230170021@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Teuku Umar No. 12, Lhokseumawe',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertRedirect(route('kiosk.index'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'Pendaftaran member berhasil. Silakan gunakan akun Anda untuk layanan mandiri.');

    $user = User::query()->where('email', '230170021@mhs.unimal.ac.id')->firstOrFail();

    expect($user->address)->toBe('Jl. Teuku Umar No. 12, Lhokseumawe')
        ->and($user->hasRole('member'))->toBeTrue()
        ->and($user->hasRequiredProfileDetails())->toBeTrue();
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
        ->assertRedirect(route('kiosk.index'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'Data kunjungan berhasil disimpan.');

    assertDatabaseHas('visit_logs', [
        'name' => 'Pengunjung Kiosk',
        'visitor_type' => VisitLog::VISITOR_TYPE_MAHASISWA,
        'identity_number' => '230170020',
        'purpose' => 'read',
    ]);
});
