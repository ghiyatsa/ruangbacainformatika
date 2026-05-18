<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    skipUnlessFortifyHas(Features::emailVerification());
});

it('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    actingAs($user)->get(route('verification.notice'))
        ->assertOk();
});

it('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();
    Cache::put("email_verification_otp_{$user->id}", Hash::make('123456'), now()->addMinutes(10));

    actingAs($user)->post(route('verification.submit'), [
        'otp' => '123456',
    ], [
        'User-Agent' => 'Verification Browser',
    ])->assertRedirect(route('home', absolute: false));

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and($user->fresh()->verified_user_agent_hash)->toBe(User::userAgentFingerprint('Verification Browser'));
});

it('email is not verified with invalid otp', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();
    Cache::put("email_verification_otp_{$user->id}", Hash::make('123456'), now()->addMinutes(10));

    actingAs($user)->post(route('verification.submit'), [
        'otp' => '654321',
    ])->assertSessionHasErrors('otp');

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('verified user is redirected to their landing page from verification prompt', function () {
    $user = User::factory()->create();

    Event::fake();

    /** @var User $user */
    actingAs($user)->get(route('verification.notice'))
        ->assertRedirect(route('home', absolute: false));

    Event::assertNotDispatched(Verified::class);
});

it('already verified user submitting otp is redirected without firing event again', function () {
    $user = User::factory()->create();

    Event::fake();

    /** @var User $user */
    actingAs($user)->post(route('verification.submit'), [
        'otp' => '123456',
    ])

        ->assertRedirect(route('home', absolute: false));

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('verified administrative users are redirected to admin after email verification', function () {
    $user = User::factory()->unverified()->create();
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole('super_admin');

    Event::fake();
    Cache::put("email_verification_otp_{$user->id}", Hash::make('123456'), now()->addMinutes(10));

    actingAs($user)->post(route('verification.submit'), [
        'otp' => '123456',
    ])

        ->assertRedirect(route('filament.admin.pages.dashboard', absolute: false));

    Event::assertDispatched(Verified::class);
});
