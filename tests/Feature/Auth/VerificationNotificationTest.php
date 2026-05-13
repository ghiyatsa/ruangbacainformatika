<?php

use App\Models\User;
use App\Notifications\Auth\VerifyEmailOtpNotification;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    skipUnlessFortifyHas(Features::emailVerification());
});

it('sends verification notification', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas(
            'status',
            'OTP verifikasi sedang dikirim ke email Anda. Jika layanan email sedang sibuk atau mencapai batas harian, sistem akan mencoba mengirim ulang secara otomatis.',
        );

    Notification::assertSentTo($user, VerifyEmailOtpNotification::class);
});

it('does not send verification notification if email is verified', function () {
    Notification::fake();

    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('home', absolute: false));

    Notification::assertNothingSent();
});

it('shows a friendly message when verification email dispatch fails', function () {
    $user = User::factory()->unverified()->create();

    $dispatcher = mock(Dispatcher::class);
    $dispatcher->shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('Daily limit exceeded by mail provider.'));

    app()->instance(Dispatcher::class, $dispatcher);

    actingAs($user)
        ->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHasErrors([
            'resend' => 'Layanan email sedang mencapai batas pengiriman harian. Kami belum bisa mengirim OTP sekarang. Silakan coba lagi beberapa saat.',
        ]);
});
