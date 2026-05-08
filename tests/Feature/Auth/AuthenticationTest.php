<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('login screen can be rendered', function () {
    get(route('login'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('auth/login')
                ->where('canResetPassword', true)
                ->where('canRegister', true)
                ->where('canLoginWithGoogle', filled(config('services.google.client_id'))
                    && filled(config('services.google.client_secret'))
                    && filled(config('services.google.redirect'))),
        );
});

it('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('home', absolute: false));

    assertAuthenticated();
});

it('administrative users are redirected to admin after login', function () {
    $user = User::factory()->create();
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole('super_admin');

    post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('filament.admin.pages.dashboard', absolute: false));

    assertAuthenticated();
});

it('authenticated users are redirected away from login screen using shared redirect logic', function () {
    $user = User::factory()->create();
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole('super_admin');

    /** @var User $user */
    actingAs($user)->get(route('login'))
        ->assertRedirect(route('filament.admin.pages.dashboard', absolute: false));
});

it('users with two factor enabled are redirected to two factor challenge', function () {
    skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('two-factor.login'))
        ->assertSessionHas('login.id', $user->id);

    assertGuest();
});

it('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    assertGuest();
});

it('users can logout', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)->post(route('logout'))
        ->assertRedirect(route('home'));

    assertGuest();
});

it('users are rate limited', function () {
    $user = User::factory()->create();

    RateLimiter::increment(strtolower($user->email).'|127.0.0.1', amount: 5);

    post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])
        ->assertSessionHasErrors('email');
});
