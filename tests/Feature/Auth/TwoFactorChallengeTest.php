<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    skipUnlessFortifyHas(Features::twoFactorAuthentication());
});

it('two factor challenge redirects to login when not authenticated', function () {
    get(route('two-factor.login'))->assertRedirect(route('login'));
});

it('two factor challenge can be rendered', function () {
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
    ]);

    get(route('two-factor.login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/two-factor-challenge'),
        );
});

