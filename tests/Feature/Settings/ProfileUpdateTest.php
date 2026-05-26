<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
});

it('profile page is displayed', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->get(route('settings.profile.edit'))
        ->assertOk();
});

it('google users with incomplete profile are redirected to onboarding page', function () {
    $user = User::factory()->create([
        'email' => '230170111@mhs.unimal.ac.id',
        'auth_provider' => 'google',
        'whatsapp' => null,
        'address' => null,
        'profile_completed_at' => null,
    ]);

    /** @var User $user */
    actingAs($user)
        ->get(route('register.profile'))
        ->assertRedirect(route('register.whatsapp'));
});

it('profile information can be updated', function () {
    $user = User::factory()->create([
        'email' => 'outside@example.com',
    ]);

    /** @var User $user */
    actingAs($user)
        ->patch(route('settings.profile.update'), [
            'name' => 'Test User',
            'email' => 'changed@example.com',
            'whatsapp' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->whatsapp)->toBe('08123456789');
    expect($user->address)->toBe('Jl. Merdeka No. 1');
    expect($user->email)->not->toBe('changed@example.com');
});

it('profile update keeps non-editable account data intact', function () {
    $user = User::factory()->create([
        'email' => 'outside@example.com',
    ]);

    /** @var User $user */
    actingAs($user)
        ->patch(route('settings.profile.update'), [
            'name' => 'Test User',
            'whatsapp' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.profile.edit'));

    expect($user->refresh()->email)->toBe($user->email);
});

it('users cannot update their email address from profile settings', function () {
    $user = User::factory()->create([
        'email' => 'outside@example.com',
    ]);

    /** @var User $user */
    actingAs($user)
        ->patch(route('settings.profile.update'), [
            'name' => 'Test User',
            'email' => 'outside@example.com',
            'whatsapp' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.profile.edit'));

    expect($user->refresh()->email)->toBe('outside@example.com');
});

it('profile update rejects invalid whatsapp and unclear address', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->patch(route('settings.profile.update'), [
            'name' => 'Test User',
            'whatsapp' => '12345',
            'address' => '???',
        ])
        ->assertSessionHasErrors([
            'whatsapp',
            'address',
        ]);
});

it('changing a verified campus whatsapp number requires re-verification', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Merdeka No. 1',
        'profile_completed_at' => now(),
        'is_approved' => true,
    ]);

    /** @var User $user */
    actingAs($user)
        ->patch(route('settings.profile.update'), [
            'name' => 'Test User',
            'whatsapp' => '08123456780',
            'address' => 'Jl. Merdeka No. 1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('register.whatsapp'));

    expect($user->fresh()?->whatsapp)->toBe('08123456780');
    expect($user->fresh()?->whatsapp_verified_at)->toBeNull();
});
