<?php

use App\Models\User;
use App\Notifications\WhatsAppOtpNotification;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
});

it('profile page is displayed', function () {
    $user = User::factory()->create([
        'whatsapp' => '081234567890',
        'address' => 'Jl. Informatika No. 10',
    ]);

    /** @var User $user */
    actingAs($user)
        ->get(route('settings.profile.edit'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('settings/profile')
                ->where('auth.user.whatsapp', $user->whatsapp)
                ->where('auth.user.address', $user->address),
        );
});

it('external users with incomplete profiles can still access profile settings', function () {
    $user = User::factory()->create([
        'email' => 'outside@example.com',
        'whatsapp' => null,
        'address' => null,
        'profile_completed_at' => null,
    ]);

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
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page) => $page->component('auth/register-profile'),
        );
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

it('allows campus users to skip profile completion and redirects to home', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => null,
        'profile_completed_at' => null,
        'is_approved' => true,
    ]);

    actingAs($user)
        ->post(route('register.profile.skip'))
        ->assertRedirect(route('home', absolute: false))
        ->assertSessionHas('profile_completion_skipped', true);
});

it('redirects skipped users back to profile completion when accessing guarded routes', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => null,
        'profile_completed_at' => null,
        'is_approved' => true,
    ]);

    actingAs($user)
        ->withSession(['profile_completion_skipped' => true])
        ->get(route('settings.member-key.show'))
        ->assertRedirect(route('register.profile', absolute: false));
});

it('allows cancelling whatsapp change and returns to profile settings', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Merdeka No. 1',
        'profile_completed_at' => now(),
        'is_approved' => true,
    ]);

    actingAs($user)
        ->withSession(['allow_whatsapp_change' => true])
        ->post(route('register.whatsapp.cancel'))
        ->assertRedirect(route('settings.profile.edit'))
        ->assertSessionMissing('allow_whatsapp_change');
});

it('stores onboarding profile and verifies whatsapp otp inline', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => null,
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => true,
    ]);

    actingAs($user)
        ->post(route('register.whatsapp.send'), [
            'whatsapp' => '08123456789',
        ])
        ->assertRedirect();

    $notification = null;

    Notification::assertSentTo($user, WhatsAppOtpNotification::class, function (WhatsAppOtpNotification $sentNotification) use (&$notification): bool {
        $notification = $sentNotification;

        return true;
    });

    preg_match('/\b(\d{6})\b/', $notification?->toWhatsApp($user)->content ?? '', $matches);
    $code = $matches[1] ?? null;

    actingAs($user)
        ->patch(route('register.profile.store'), [
            'name' => 'Mahasiswa Inline',
            'whatsapp' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
            'code' => $code,
        ])
        ->assertRedirect(route('home', absolute: false));

    $user->refresh();

    expect($user->name)->toBe('Mahasiswa Inline');
    expect($user->address)->toBe('Jl. Merdeka No. 1');
    expect($user->whatsapp)->toBe('08123456789');
    expect($user->whatsapp_verified_at)->not->toBeNull();
    expect($user->hasCompletedProfile())->toBeTrue();
});

it('allows sending and verifying whatsapp otp directly from profile settings modal', function () {
    Notification::fake();

    $user = User::factory()->create([
        'whatsapp' => null,
        'whatsapp_verified_at' => null,
    ]);

    actingAs($user)
        ->from(route('settings.profile.edit'))
        ->post(route('settings.profile.whatsapp.send'), [
            'whatsapp' => '08123456789',
        ])
        ->assertRedirect(route('settings.profile.edit'))
        ->assertSessionHas('inertia.flash_data.toast.type', 'success');

    $user->refresh();
    expect($user->whatsapp)->toBeNull();

    $notification = null;
    Notification::assertSentTo($user, WhatsAppOtpNotification::class, function (WhatsAppOtpNotification $sentNotification) use (&$notification): bool {
        $notification = $sentNotification;

        return true;
    });

    expect($notification)->not->toBeNull();

    preg_match('/\b(\d{6})\b/', $notification?->toWhatsApp($user)->content ?? '', $matches);
    $code = $matches[1] ?? null;

    actingAs($user)
        ->from(route('settings.profile.edit'))
        ->post(route('settings.profile.whatsapp.verify'), [
            'code' => $code,
        ])
        ->assertRedirect(route('settings.profile.edit'))
        ->assertSessionHas('inertia.flash_data.toast.type', 'success');

    $user->refresh();
    expect($user->whatsapp)->toBe('08123456789');
    expect($user->whatsapp_verified_at)->not->toBeNull();
});

it('retains original verified whatsapp number if new number otp verification is not yet completed', function () {
    Notification::fake();

    $originalVerifiedAt = now()->subDays(5);
    $user = User::factory()->create([
        'whatsapp' => '08111111111',
        'whatsapp_verified_at' => $originalVerifiedAt,
    ]);

    // Send OTP for new number
    actingAs($user)
        ->withSession(['allow_whatsapp_change' => true])
        ->from(route('settings.profile.edit'))
        ->post(route('settings.profile.whatsapp.send'), [
            'whatsapp' => '08222222222',
        ])
        ->assertRedirect(route('settings.profile.edit'))
        ->assertSessionHas('inertia.flash_data.toast.type', 'success');

    // Number must STILL be the original verified number!
    $user->refresh();
    expect($user->whatsapp)->toBe('08111111111');
    expect($user->whatsapp_verified_at)->not->toBeNull();

    // Try verifying with wrong code
    actingAs($user)
        ->withSession(['allow_whatsapp_change' => true])
        ->from(route('settings.profile.edit'))
        ->post(route('settings.profile.whatsapp.verify'), [
            'code' => '000000',
        ])
        ->assertSessionHasErrors(['code']);

    // Still the original verified number!
    $user->refresh();
    expect($user->whatsapp)->toBe('08111111111');
    expect($user->whatsapp_verified_at)->not->toBeNull();

    // Now verify with correct code
    $notification = null;
    Notification::assertSentTo($user, WhatsAppOtpNotification::class, function (WhatsAppOtpNotification $sentNotification) use (&$notification): bool {
        $notification = $sentNotification;

        return true;
    });

    preg_match('/\b(\d{6})\b/', $notification?->toWhatsApp($user)->content ?? '', $matches);
    $correctCode = $matches[1] ?? null;

    actingAs($user)
        ->withSession(['allow_whatsapp_change' => true])
        ->from(route('settings.profile.edit'))
        ->post(route('settings.profile.whatsapp.verify'), [
            'code' => $correctCode,
        ])
        ->assertRedirect(route('settings.profile.edit'));

    // Now the number should be safely updated to the new verified number
    $user->refresh();
    expect($user->whatsapp)->toBe('08222222222');
    expect($user->whatsapp_verified_at)->not->toBeNull();
});
