<?php

use App\Models\MemberRegistrationClaim;
use App\Models\User;
use App\Services\MemberRegistrationClaimService;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

beforeEach(function () {
    config()->set('services.google', [
        'client_id' => 'google-client-id',
        'client_secret' => 'google-client-secret',
        'redirect' => 'http://localhost/auth/google/callback',
    ]);
});

it('redirects a valid account-link URL straight to google auth', function () {
    $token = 'rb-link-test-token';

    MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(30),
    ]);

    get(route('account-links.show', ['token' => $token]))
        ->assertRedirect(route('auth.google', ['link_token' => $token], absolute: false));
});

it('completes a kiosk account link through google login', function () {
    $token = 'rb-link-test-token';

    $claim = MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(30),
    ]);

    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Mahasiswa TI',
        'email' => '230170001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google', ['link_token' => $token]))
        ->assertRedirect();

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.whatsapp', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => '230170001@mhs.unimal.ac.id',
        'google_id' => 'google-123',
        'name' => 'Member Kiosk',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'auth_provider' => 'google',
    ]);

    assertDatabaseHas('member_registration_claims', [
        'id' => $claim->id,
        'status' => MemberRegistrationClaim::STATUS_LINKED,
    ]);

    $user = User::query()->where('email', '230170001@mhs.unimal.ac.id')->firstOrFail();

    expect($user->whatsapp)->toBe('08123456789')
        ->and($claim->fresh()->user_id)->toBe($user->id);
});

it('completes a non teknik informatika kiosk account link without forcing whatsapp before admin approval', function () {
    $token = 'rb-link-non-ti-token';

    $claim = MemberRegistrationClaim::query()->create([
        'name' => 'Member Non TI',
        'email' => '230160001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 2',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(30),
    ]);

    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-non-ti-123',
        'name' => 'Mahasiswa Non TI',
        'email' => '230160001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google', ['link_token' => $token]))
        ->assertRedirect();

    get(route('auth.google.callback'))
        ->assertRedirect(route('home', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => '230160001@mhs.unimal.ac.id',
        'google_id' => 'google-non-ti-123',
        'name' => 'Member Non TI',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 2',
        'auth_provider' => 'google',
        'is_approved' => false,
    ]);

    assertDatabaseHas('member_registration_claims', [
        'id' => $claim->id,
        'status' => MemberRegistrationClaim::STATUS_CLAIMED,
    ]);
});

it('marks a linked kiosk account-link claim as claimed after whatsapp verification finishes', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'whatsapp_verified_at' => now(),
        'is_approved' => true,
    ]);

    $claim = MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => $user->email,
        'whatsapp' => $user->whatsapp,
        'address' => $user->address,
        'token_hash' => hash('sha256', 'rb-link-verified'),
        'expires_at' => now()->addMinutes(30),
        'status' => MemberRegistrationClaim::STATUS_LINKED,
        'user_id' => $user->id,
    ]);

    app(MemberRegistrationClaimService::class)->markLinkedClaimAsVerified($user);

    expect($claim->fresh()?->status)->toBe(MemberRegistrationClaim::STATUS_CLAIMED);
    expect($claim->fresh()?->claimed_at)->not->toBeNull();
});

it('records the latest claim error when the selected google email does not match', function () {
    $token = 'rb-link-test-mismatch';

    MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(30),
    ]);

    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-456',
        'name' => 'Mahasiswa Lain',
        'email' => '230160001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google', ['link_token' => $token]))
        ->assertRedirect();

    get(route('auth.google.callback'))
        ->assertRedirect(route('home', absolute: false));

    assertDatabaseHas('member_registration_claims', [
        'email' => '230170001@mhs.unimal.ac.id',
        'status' => MemberRegistrationClaim::STATUS_PENDING,
        'last_error_message' => 'Gunakan akun Google dengan email UNIMAL yang sama seperti data registrasi.',
    ]);
});

it('generates member registration qr svg with transparent background and current color foreground', function () {
    $service = app(MemberRegistrationClaimService::class);
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('generateQrSvg');
    $method->setAccessible(true);

    $svg = $method->invoke($service, 'https://ruangbaca.test/account-link/rb-link-theme');

    expect($svg)
        ->toContain('fill="var(--foreground)"')
        ->toContain('fill="var(--background)"')
        ->not->toContain('#111827')
        ->not->toContain('#ffffff');
});

it('refreshes old member registration qr svg markup when syncing an active claim', function () {
    $token = 'rb-link-refresh';

    $claim = MemberRegistrationClaim::query()->create([
        'name' => 'Member Kiosk',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Kampus No. 1',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(30),
        'status' => MemberRegistrationClaim::STATUS_LINKED,
        'user_id' => User::factory()->create([
            'email' => '230170001@mhs.unimal.ac.id',
            'whatsapp' => '08123456789',
            'address' => 'Jl. Kampus No. 1',
            'whatsapp_verified_at' => now(),
            'is_approved' => true,
        ])->id,
    ]);

    $service = app(MemberRegistrationClaimService::class);

    $synced = $service->syncPresentedClaim([
        'id' => $claim->id,
        'name' => $claim->name,
        'email' => $claim->email,
        'whatsapp' => $claim->whatsapp,
        'address' => $claim->address,
        'linkUrl' => 'https://ruangbaca.test/account-link/'.$token,
        'qrSvg' => '<svg><rect fill="#ffffff"></rect><path fill="#111827"></path></svg>',
    ]);

    expect($synced)
        ->not->toBeNull()
        ->and($synced['status'])
        ->toBe(MemberRegistrationClaim::STATUS_CLAIMED)
        ->and($synced['qrSvg'])
        ->toContain('var(--foreground)')
        ->not->toContain('#111827')
        ->not->toContain('#ffffff');
});
