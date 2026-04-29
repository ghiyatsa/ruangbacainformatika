<?php

use App\Http\Responses\Auth\RegisterResponse;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/register')
        ->where('canLoginWithGoogle', filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'))),
    );
});

test('new users can register', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('users cannot register with non-campus email', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('register'));
});

test('mahasiswa outside teknik informatika cannot register', function () {
    $response = $this->from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => '230160001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('register'));
});

test('manual registration stores whatsapp when provided', function () {
    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => '230170009@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', '230170009@mhs.unimal.ac.id')->firstOrFail();

    expect($user->whatsapp)->toBe('08123456789');
});

test('kiosk registration redirects to email verification and returns to kiosk after verification', function () {
    $response = $this->post(route('register'), [
        'name' => 'Kiosk User',
        'email' => '230170010@mhs.unimal.ac.id',
        'password' => 'password',
        'password_confirmation' => 'password',
        'redirect_to' => route('kiosk.index', absolute: false),
    ]);

    $user = User::query()->where('email', '230170010@mhs.unimal.ac.id')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('verification.notice', absolute: false));
    $this->assertTrue(session()->has(RegisterResponse::KIOSK_RETURN_AFTER_VERIFICATION_KEY));

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this->actingAs($user)
        ->withSession([RegisterResponse::KIOSK_RETURN_AFTER_VERIFICATION_KEY => true])
        ->get($verificationUrl)
        ->assertRedirect(route('kiosk.index'));
});
