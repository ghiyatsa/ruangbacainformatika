<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

beforeEach(function () {
    config()->set('services.google', [
        'client_id' => 'google-client-id',
        'client_secret' => 'google-client-secret',
        'redirect' => 'http://localhost/auth/google/callback',
    ]);
});

it('google login button is enabled when google is configured', function () {
    get(route('login'))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/login')
                ->where('canLoginWithGoogle', true),
        );
});

it('google registration button is enabled when google is configured', function () {
    get(route('register'))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/register')
                ->where('canLoginWithGoogle', true),
        );
});

it('users are redirected to google', function () {
    Socialite::fake('google');

    get(route('auth.google'))
        ->assertRedirect();
});

it('eligible users can authenticate with google', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Mahasiswa TI',
        'email' => '230170001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('register.profile', absolute: false));

    assertAuthenticated();

    assertDatabaseHas('users', [
        'email' => '230170001@mhs.unimal.ac.id',
        'name' => 'Mahasiswa TI',
        'auth_provider' => 'google',
        'is_approved' => true,
    ]);

    $user = User::query()->where('email', '230170001@mhs.unimal.ac.id')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeTrue();
    expect($user->whatsapp)->toBeNull();
    expect($user->address)->toBeNull();
});

it('administrative users with complete profiles are redirected to admin after google login', function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'profile_completed_at' => now(),
        'email_verified_at' => now(),
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

it('users with invalid google email are redirected back to login', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-456',
        'name' => 'Outside User',
        'email' => 'outside@example.com',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('login'));

    assertGuest();

    assertDatabaseMissing('users', [
        'email' => 'outside@example.com',
    ]);
});

it('mahasiswa outside teknik informatika are redirected back to login', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-789',
        'name' => 'Mahasiswa Non TI',
        'email' => '230160001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    get(route('auth.google.callback'))
        ->assertRedirect(route('login'));

    assertGuest();

    assertDatabaseMissing('users', [
        'email' => '230160001@mhs.unimal.ac.id',
    ]);
});

it('google users can access onboarding only once', function () {
    $user = User::factory()->create([
        'auth_provider' => 'google',
        'whatsapp' => null,
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

it('legacy users with whatsapp only can complete onboarding by adding an address', function () {
    $user = User::factory()->create([
        'whatsapp' => '08123456789',
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
