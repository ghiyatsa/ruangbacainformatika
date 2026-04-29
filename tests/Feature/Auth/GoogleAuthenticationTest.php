<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    config()->set('services.google', [
        'client_id' => 'google-client-id',
        'client_secret' => 'google-client-secret',
        'redirect' => 'http://localhost/auth/google/callback',
    ]);
});

test('google login button is enabled when google is configured', function () {
    $this->get(route('login'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('auth/login')
            ->where('canLoginWithGoogle', true),
        );
});

test('google registration button is enabled when google is configured', function () {
    $this->get(route('register'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('auth/register')
            ->where('canLoginWithGoogle', true),
        );
});

test('users are redirected to google', function () {
    Socialite::fake('google');

    $this->get(route('auth.google'))
        ->assertRedirect();
});

test('eligible users can authenticate with google', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Mahasiswa TI',
        'email' => '230170001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $this->assertAuthenticated();
    $response->assertRedirect(route('register.whatsapp', absolute: false));

    $this->assertDatabaseHas('users', [
        'email' => '230170001@mhs.unimal.ac.id',
        'name' => 'Mahasiswa TI',
        'auth_provider' => 'google',
        'is_approved' => true,
    ]);

    $user = User::query()->where('email', '230170001@mhs.unimal.ac.id')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeTrue();
    expect($user->whatsapp)->toBeNull();
});

test('administrative users with complete profiles are redirected to admin after google login', function () {
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

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('filament.admin.pages.dashboard', absolute: false));
});

test('users with invalid google email are redirected back to login', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-456',
        'name' => 'Outside User',
        'email' => 'outside@example.com',
    ]);

    Socialite::fake('google', $socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));

    $this->assertDatabaseMissing('users', [
        'email' => 'outside@example.com',
    ]);
});

test('mahasiswa outside teknik informatika are redirected back to login', function () {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => 'google-789',
        'name' => 'Mahasiswa Non TI',
        'email' => '230160001@mhs.unimal.ac.id',
    ]);

    Socialite::fake('google', $socialiteUser);

    $response = $this->get(route('auth.google.callback'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));

    $this->assertDatabaseMissing('users', [
        'email' => '230160001@mhs.unimal.ac.id',
    ]);
});

test('google users can access onboarding only once', function () {
    $user = User::factory()->create([
        'auth_provider' => 'google',
        'whatsapp' => null,
        'profile_completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('auth/register-whatsapp'),
        );

    $this->actingAs($user)
        ->patch(route('register.whatsapp.store'), [
            'whatsapp' => '08123456789',
        ])
        ->assertRedirect(route('settings.profile.edit', absolute: false));

    $this->actingAs($user->fresh())
        ->get(route('register.whatsapp'))
        ->assertRedirect(route('settings.profile.edit', absolute: false));
});
