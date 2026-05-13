<?php

use App\Http\Responses\Auth\RegisterResponse;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    skipUnlessFortifyHas(Features::registration());
});

it('registration screen can be rendered', function () {
    get(route('register'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('auth/register')
                ->where('canLoginWithGoogle', filled(config('services.google.client_id'))
                    && filled(config('services.google.client_secret'))
                    && filled(config('services.google.redirect'))),
        );
});

it('new users can register', function () {
    post(route('register'), [
        'name' => 'Test User',
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertRedirect(route('verification.notice', absolute: false))
        ->assertSessionHas(
            'status',
            'OTP verifikasi sedang dikirim ke email Anda. Jika layanan email sedang sibuk atau mencapai batas harian, sistem akan mencoba mengirim ulang secara otomatis.',
        );

    assertAuthenticated();
});

it('new users can register with name prefix in email', function () {
    post(route('register'), [
        'name' => 'Zakiatunniza',
        'email' => 'zakiatunniza.230170013@mhs.unimal.ac.id',
        'whatsapp' => '08123456780',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('verification.notice', absolute: false));

    assertAuthenticated();
});

it('users cannot register with non-campus email', function () {
    from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('register'));
});

it('mahasiswa outside teknik informatika cannot register', function () {
    from(route('register'))->post(route('register'), [
        'name' => 'Test User',
        'email' => '230160001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('register'));
});

it('manual registration stores whatsapp when provided', function () {
    post(route('register'), [
        'name' => 'Test User',
        'email' => '230170009@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', '230170009@mhs.unimal.ac.id')->firstOrFail();

    expect($user->whatsapp)->toBe('08123456789');
});

it('kiosk registration redirects to email verification and returns to kiosk after verification', function () {
    post(route('register'), [
        'name' => 'Kiosk User',
        'email' => '230170010@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'password' => 'password',
        'password_confirmation' => 'password',
        'redirect_to' => route('kiosk.index', absolute: false),
    ])->assertRedirect(route('verification.notice', absolute: false));

    $user = User::query()->where('email', '230170010@mhs.unimal.ac.id')->firstOrFail();

    assertAuthenticatedAs($user);
    expect(session()->has(RegisterResponse::KIOSK_RETURN_AFTER_VERIFICATION_KEY))->toBeTrue();

    Cache::put("email_verification_otp_{$user->id}", '123456', now()->addMinutes(10));

    actingAs($user)
        ->withSession([RegisterResponse::KIOSK_RETURN_AFTER_VERIFICATION_KEY => true])
        ->post(route('verification.submit'), [
            'otp' => '123456',
        ])
        ->assertRedirect(route('kiosk.index'));
});
