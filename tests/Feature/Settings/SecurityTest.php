<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

use function Pest\Laravel\actingAs;

it('security page is displayed', function () {
    skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();
    /** @var User $user */
    actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('settings.security.edit'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('settings/security')
                ->where('canManageTwoFactor', true)
                ->where('twoFactorEnabled', false),
        );
});

it('security page requires password confirmation when enabled', function () {
    skipUnlessFortifyHas(Features::twoFactorAuthentication());

    $user = User::factory()->create();

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    /** @var User $user */
    actingAs($user)
        ->get(route('settings.security.edit'))
        ->assertRedirect(route('password.confirm'));
});

it('security page does not require password confirmation when disabled', function () {
    skipUnlessFortifyHas(Features::twoFactorAuthentication());

    $user = User::factory()->create();

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => false,
    ]);

    /** @var User $user */
    actingAs($user)
        ->get(route('settings.security.edit'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('settings/security'),
        );
});

it('security page renders without two factor when feature is disabled', function () {
    skipUnlessFortifyHas(Features::twoFactorAuthentication());

    config(['fortify.features' => []]);

    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->get(route('settings.security.edit'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('settings/security')
                ->where('canManageTwoFactor', false)
                ->missing('twoFactorEnabled')
                ->missing('requiresConfirmation'),
        );
});

it('password can be updated', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->from(route('settings.security.edit'))
        ->put(route('settings.password.update'), [
            'current_password' => 'password',
            'password' => 'PasswordAman123!',
            'password_confirmation' => 'PasswordAman123!',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.security.edit'));

    expect(Hash::check('PasswordAman123!', $user->refresh()->password))->toBeTrue();
});

it('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->from(route('settings.security.edit'))
        ->put(route('settings.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'PasswordAman123!',
            'password_confirmation' => 'PasswordAman123!',
        ])
        ->assertSessionHasErrors('current_password')
        ->assertRedirect(route('settings.security.edit'));
});

it('password update requires a strong password', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->from(route('settings.security.edit'))
        ->put(route('settings.password.update'), [
            'current_password' => 'password',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('settings.security.edit'));
});

it('password update accepts password that meets the minimum rule', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->from(route('settings.security.edit'))
        ->put(route('settings.password.update'), [
            'current_password' => 'password',
            'password' => 'member123',
            'password_confirmation' => 'member123',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.security.edit'));

    expect(Hash::check('member123', $user->refresh()->password))->toBeTrue();
});
