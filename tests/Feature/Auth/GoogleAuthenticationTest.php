<?php

use App\Models\User;
use App\Services\GoogleIdTokenVerifier;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Testing\AssertableInertia;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
    config()->set('services.google', [
        'client_id' => 'google-client-id',
        'client_secret' => 'google-client-secret',
        'redirect' => 'http://localhost/auth/google/callback',
    ]);
});

it('google login button is enabled when google is configured', function () {
    get(route('login'))->assertRedirect(route('auth.google', absolute: false));
});

it('register route redirects directly to google when google is configured', function () {
    get(route('register'))->assertRedirect(route('auth.google', absolute: false));
});

it('eligible users can authenticate with google one tap', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    User::factory()->create([
        'email' => '230170007@mhs.unimal.ac.id',
        'profile_completed_at' => null,
        'whatsapp' => null,
        'address' => null,
        'is_approved' => false,
    ]);

    app()->instance(GoogleIdTokenVerifier::class, new class extends GoogleIdTokenVerifier
    {
        public function verify(string $credential): array
        {
            expect($credential)->toBe('one-tap-token');

            return [
                'sub' => 'google-one-tap-123',
                'email' => '230170007@mhs.unimal.ac.id',
                'name' => 'Mahasiswa One Tap',
            ];
        }
    });

    post(route('auth.google.one-tap'), [
        'credential' => 'one-tap-token',
    ])->assertRedirect(route('register.whatsapp', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => '230170007@mhs.unimal.ac.id',
        'google_id' => 'google-one-tap-123',
        'auth_provider' => 'google',
        'is_approved' => false,
    ]);
    expect(User::query()->where('email', '230170007@mhs.unimal.ac.id')->firstOrFail()->hasRole('member'))->toBeFalse();
});

it('users are redirected to google', function () {
    Socialite::fake('google');

    get(route('auth.google'))
        ->assertRedirect();
});

it('eligible users can authenticate with google', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => null,
        'address' => null,
        'profile_completed_at' => null,
        'is_approved' => false,
    ]);

    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Mahasiswa TI',
        'email' => '230170001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.whatsapp', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => '230170001@mhs.unimal.ac.id',
        'name' => 'Mahasiswa TI',
        'google_id' => 'google-123',
        'auth_provider' => 'google',
        'is_approved' => false,
    ]);

    $user = User::query()->where('email', '230170001@mhs.unimal.ac.id')->firstOrFail();

    expect($user->whatsapp)->toBeNull();
    expect($user->address)->toBeNull();
    expect($user->hasRole('member'))->toBeFalse();
});

it('unknown users can create a new account through direct google login', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-999',
        'name' => 'Mahasiswa Baru',
        'email' => '230170999@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.whatsapp', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => '230170999@mhs.unimal.ac.id',
        'google_id' => 'google-999',
        'name' => 'Mahasiswa Baru',
        'is_approved' => false,
    ]);

    expect(User::query()->where('email', '230170999@mhs.unimal.ac.id')->firstOrFail()->hasRole('member'))->toBeFalse();
});

it('non student campus accounts require manual approval after google login', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-998',
        'name' => 'Dosen Baru',
        'email' => 'dosen@unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.whatsapp', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => 'dosen@unimal.ac.id',
        'google_id' => 'google-998',
        'is_approved' => false,
    ]);
});

it('administrative users with complete profiles are redirected to admin after google login', function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'is_approved' => true,
        'profile_completed_at' => now(),
    ])->assignRole('super_admin');

    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Mahasiswa TI',
        'email' => '230170001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('filament.admin.pages.dashboard', absolute: false));
});

it('administrative users with complete profiles receive an inertia location response after google one tap login', function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'is_approved' => true,
        'profile_completed_at' => now(),
    ])->assignRole('super_admin');

    app()->instance(GoogleIdTokenVerifier::class, new class extends GoogleIdTokenVerifier
    {
        public function verify(string $credential): array
        {
            expect($credential)->toBe('one-tap-admin-token');

            return [
                'sub' => 'google-admin-123',
                'email' => '230170001@mhs.unimal.ac.id',
                'name' => 'Super Admin',
            ];
        }
    });

    post(route('auth.google.one-tap'), [
        'credential' => 'one-tap-admin-token',
    ], [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
    ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', route('filament.admin.pages.dashboard'));
});

it('public users with a valid external google email can still sign in', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-456',
        'name' => 'Outside User',
        'email' => 'outside@example.com',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.profile', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => 'outside@example.com',
        'google_id' => 'google-456',
        'is_approved' => false,
    ]);
});

it('non teknik informatika student accounts can sign in but do not receive borrowing access', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-789',
        'name' => 'Mahasiswa Non TI',
        'email' => '230160001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.whatsapp', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => '230160001@mhs.unimal.ac.id',
        'is_approved' => false,
    ]);

    $user = User::query()->where('email', '230160001@mhs.unimal.ac.id')->firstOrFail();

    expect($user->hasRole('member'))->toBeFalse();
    expect($user->canBorrowBooks())->toBeFalse();
});

it('google users can access onboarding only once', function () {
    $user = User::factory()->create([
        'auth_provider' => 'google',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => null,
        'profile_completed_at' => null,
    ]);

    /** @var User $user */
    actingAs($user)
        ->get(route('register.profile'))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/register-profile'),
        );

    actingAs($user)
        ->patch(route('register.profile.store'), [
            'name' => $user->name,
            'whatsapp' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
        ])
        ->assertRedirect(route('settings.profile.edit', absolute: false));

    $user->refresh();

    /** @var User $user */
    actingAs($user)
        ->get(route('register.profile'))
        ->assertRedirect(route('settings.profile.edit', absolute: false));
});

it('campus users with complete profiles are redirected to whatsapp verification after google login', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Merdeka No. 1',
        'profile_completed_at' => Carbon::yesterday(),
        'whatsapp_verified_at' => null,
        'is_approved' => false,
    ]);

    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-otp-001',
        'name' => 'Mahasiswa TI',
        'email' => $user->email,
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.whatsapp', absolute: false));
});

it('campus users without complete profiles are also redirected to whatsapp verification after google login', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-otp-002',
        'name' => 'Mahasiswa TI Baru',
        'email' => '230170123@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.whatsapp', absolute: false));
});

it('legacy users with whatsapp only can complete onboarding by adding an address', function () {
    $user = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => null,
        'profile_completed_at' => null,
    ]);

    /** @var User $user */
    actingAs($user)
        ->get(route('register.profile'))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/register-profile')
                ->where('auth.user.whatsapp', '08123456789')
                ->where('auth.user.address', null),
        );

    /** @var User $user */
    actingAs($user)
        ->patch(route('register.profile.store'), [
            'name' => $user->name,
            'address' => 'Jl. Merdeka No. 1',
        ])
        ->assertRedirect(route('settings.profile.edit', absolute: false));

    assertDatabaseHas('users', [
        'id' => $user->id,
        'whatsapp' => '08123456789',
        'address' => 'Jl. Merdeka No. 1',
    ]);

    expect($user->fresh()->profile_completed_at)->not->toBeNull();
});

it('onboarding rejects invalid whatsapp and unclear address', function () {
    $user = User::factory()->create([
        'auth_provider' => 'google',
        'whatsapp' => null,
        'address' => null,
        'profile_completed_at' => null,
    ]);

    /** @var User $user */
    actingAs($user)
        ->patch(route('register.profile.store'), [
            'name' => $user->name,
            'whatsapp' => '12345',
            'address' => '???',
        ])
        ->assertSessionHasErrors([
            'whatsapp',
            'address',
        ]);
});
