<?php

use App\Models\User;
use App\Notifications\Auth\VerifyEmailOtpNotification;
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
        ->assertRedirect(route('verification.notice'));

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
