<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('google users with incomplete profile are redirected to onboarding page', function () {
    $user = User::factory()->create([
        'auth_provider' => 'google',
        'whatsapp' => null,
        'profile_completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/register-whatsapp'),
        );
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'changed@example.com',
            'whatsapp' => '08123456789',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->whatsapp)->toBe('08123456789');
    expect($user->email)->not->toBe('changed@example.com');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'whatsapp' => '08123456789',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('users cannot update their email address from profile settings', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'outside@example.com',
            'whatsapp' => '08123456789',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email)->toBe('230170001@mhs.unimal.ac.id');
    expect($user->email_verified_at)->not->toBeNull();
});

test('non-administrative users cannot delete their account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response->assertForbidden();

    $this->assertAuthenticatedAs($user);
    expect($user->fresh())->not->toBeNull();
});

test('administrative users can delete their account', function () {
    Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('staff');

    $response = $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});
