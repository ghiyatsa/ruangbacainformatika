<?php

use App\Models\User;
use App\Notifications\WhatsAppOtpNotification;
use App\Services\WhatsAppGateway;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $gateway = mock(WhatsAppGateway::class);
    $gateway->shouldReceive('configured')->andReturn(true);
    app()->instance(WhatsAppGateway::class, $gateway);
});

it('renders the whatsapp verification page for campus users before profile completion', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => false,
    ]);

    actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertSuccessful()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/verify-whatsapp')
                ->where('verification.approvalMode', 'automatic')
                ->where('verification.hasActiveChallenge', true),
        );

    Notification::assertSentTo($user, WhatsAppOtpNotification::class);
});

it('verifying whatsapp otp auto-approves teknik informatika students', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => false,
    ]);

    actingAs($user)->get(route('register.whatsapp'));

    $notification = null;

    Notification::assertSentTo($user, WhatsAppOtpNotification::class, function (WhatsAppOtpNotification $sentNotification) use (&$notification): bool {
        $notification = $sentNotification;

        return true;
    });

    preg_match('/\b(\d{6})\b/', $notification?->toWhatsApp($user)->content ?? '', $matches);
    $code = $matches[1] ?? null;

    expect($code)->not->toBeNull();

    actingAs($user)
        ->post(route('register.whatsapp.verify'), [
            'code' => $code,
        ])
        ->assertRedirect(route('register.profile', absolute: false));

    assertDatabaseHas('users', [
        'id' => $user->id,
        'is_approved' => true,
    ]);

    expect($user->fresh()?->whatsapp_verified_at)->not->toBeNull();
    expect($user->fresh()?->canBorrowBooks())->toBeTrue();
});

it('verifying whatsapp otp keeps non student campus accounts pending admin approval', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'dosen@unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => false,
    ]);

    actingAs($user)->get(route('register.whatsapp'));

    $notification = null;

    Notification::assertSentTo($user, WhatsAppOtpNotification::class, function (WhatsAppOtpNotification $sentNotification) use (&$notification): bool {
        $notification = $sentNotification;

        return true;
    });

    preg_match('/\b(\d{6})\b/', $notification?->toWhatsApp($user)->content ?? '', $matches);
    $code = $matches[1] ?? null;

    expect($code)->not->toBeNull();

    actingAs($user)
        ->post(route('register.whatsapp.verify'), [
            'code' => $code,
        ])
        ->assertRedirect(route('register.profile', absolute: false));

    assertDatabaseHas('users', [
        'id' => $user->id,
        'is_approved' => false,
    ]);

    expect($user->fresh()?->whatsapp_verified_at)->not->toBeNull();
    expect($user->fresh()?->requiresManualApproval())->toBeTrue();
    expect($user->fresh()?->canBorrowBooks())->toBeFalse();
});

it('sending whatsapp otp can store the number first for campus users', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => null,
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => false,
    ]);

    actingAs($user)
        ->post(route('register.whatsapp.send'), [
            'whatsapp' => '08123456789',
        ])
        ->assertSessionHasNoErrors();

    assertDatabaseHas('users', [
        'id' => $user->id,
        'whatsapp' => '08123456789',
    ]);

    Notification::assertSentTo($user->fresh(), WhatsAppOtpNotification::class);
});

it('public users are redirected away from whatsapp verification', function () {
    $user = User::factory()->create([
        'email' => 'outside@example.com',
        'whatsapp' => '08123456789',
        'address' => 'Jl. Umum No. 2',
        'profile_completed_at' => now(),
        'whatsapp_verified_at' => null,
        'is_approved' => false,
    ]);

    actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertRedirect(route('settings.profile.edit', absolute: false));
});
