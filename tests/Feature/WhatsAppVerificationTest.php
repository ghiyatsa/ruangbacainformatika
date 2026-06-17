<?php

use App\Models\User;
use App\Notifications\WhatsAppOtpNotification;
use App\Services\WhatsAppGateway;
use App\Services\WhatsAppOtpService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
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
        'is_approved' => true,
    ]);

    actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertSuccessful()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/verify-whatsapp')
                ->where('verification.approvalMode', 'automatic')
                ->where('verification.approvalMessage', 'Verifikasi WhatsApp akan melengkapi status anggota Anda.')
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
        'is_approved' => true,
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

it('unapproved non student campus accounts are redirected away from whatsapp verification', function () {
    $user = User::factory()->create([
        'email' => 'dosen@unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => false,
    ]);

    actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertRedirect(route('home', absolute: false));
});

it('approved non student campus accounts can verify whatsapp and gain borrowing access', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'dosen@unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => true,
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
    expect($user->fresh()?->requiresManualApproval())->toBeFalse();
    expect($user->fresh()?->canBorrowBooks())->toBeTrue();
});

it('approved non student campus accounts no longer see manual approval guidance on the whatsapp verification page', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'dosen@unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => true,
    ]);

    actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertSuccessful()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/verify-whatsapp')
                ->where('verification.approvalMode', 'automatic')
                ->where('verification.approvalMessage', 'Verifikasi WhatsApp akan melengkapi status anggota Anda.'),
        );
});

it('approved non teknik informatika student accounts can verify whatsapp after admin approval', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => '230160001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => true,
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

    expect($user->fresh()?->whatsapp_verified_at)->not->toBeNull();
    expect($user->fresh()?->canBorrowBooks())->toBeTrue();
});

it('unapproved non teknik informatika student accounts are redirected away from whatsapp verification', function () {
    $user = User::factory()->create([
        'email' => '230160001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => false,
    ]);

    actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertRedirect(route('home', absolute: false));
});

it('sending whatsapp otp can store the number first for campus users', function () {
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
        ->assertRedirect(route('home', absolute: false));
});

it('escalates the whatsapp otp send cooldown dynamically as send requests increase', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => true,
    ]);

    // First send - cooldown should be 60 seconds (1 minute)
    actingAs($user)
        ->post(route('register.whatsapp.send'))
        ->assertSessionHasNoErrors();

    $status = app(WhatsAppOtpService::class)->status($user);
    expect($status['resendAvailableIn'])->toBeGreaterThan(0)
        ->and($status['resendAvailableIn'])->toBeLessThanOrEqual(60);

    // Clear cooldown to allow second dispatch attempt
    RateLimiter::clear('whatsapp-otp:cooldown:'.$user->id);

    // Second send - cooldown should be 120 seconds (2 minutes)
    actingAs($user)
        ->post(route('register.whatsapp.send'))
        ->assertSessionHasNoErrors();

    $status = app(WhatsAppOtpService::class)->status($user);
    expect($status['resendAvailableIn'])->toBeGreaterThan(60)
        ->and($status['resendAvailableIn'])->toBeLessThanOrEqual(120);
});

it('allows verified campus users to access verification page when session has allow_whatsapp_change', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus',
        'profile_completed_at' => now(),
        'is_approved' => true,
    ]);

    // Without session flag - should redirect
    actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertRedirect(route('home', absolute: false));

    // With session flag - should load successfully
    actingAs($user)
        ->withSession(['allow_whatsapp_change' => true])
        ->get(route('register.whatsapp'))
        ->assertSuccessful()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('auth/verify-whatsapp')
        );
});

it('redirects administrative campus users away from whatsapp onboarding by default', function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => null,
        'whatsapp_verified_at' => null,
        'address' => null,
        'profile_completed_at' => null,
        'is_approved' => true,
    ]);
    $user->assignRole('super_admin');
    $user->assignRole('staff');
    $user->assignRole('member');

    actingAs($user)
        ->get(route('register.whatsapp'))
        ->assertRedirect(route('home', absolute: false));
});

it('always redirects non-campus users even with session flag', function () {
    $user = User::factory()->create([
        'email' => 'outside@example.com',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Umum',
        'profile_completed_at' => now(),
        'is_approved' => true,
    ]);

    actingAs($user)
        ->withSession(['allow_whatsapp_change' => true])
        ->get(route('register.whatsapp'))
        ->assertRedirect(route('home', absolute: false));
});

it('initiates whatsapp change by setting session and redirecting', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus',
        'profile_completed_at' => now(),
        'is_approved' => true,
    ]);

    actingAs($user)
        ->post(route('settings.profile.change-whatsapp'))
        ->assertRedirect(route('register.whatsapp', absolute: false))
        ->assertSessionHas('allow_whatsapp_change', true);
});

it('prevents sending otp if the new whatsapp number is identical to current verified number', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus',
        'profile_completed_at' => now(),
        'is_approved' => true,
    ]);

    actingAs($user)
        ->withSession(['allow_whatsapp_change' => true])
        ->post(route('register.whatsapp.send'), [
            'whatsapp' => '08123456789',
        ])
        ->assertSessionHasErrors('whatsapp');

    Notification::assertNothingSent();
});

it('rejects duplicate whatsapp numbers after canonical normalization before sending otp', function () {
    Notification::fake();

    User::factory()->create([
        'email' => 'existing@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
    ]);

    $user = User::factory()->create([
        'email' => '230170099@mhs.unimal.ac.id',
        'whatsapp' => null,
        'whatsapp_verified_at' => null,
        'address' => null,
        'profile_completed_at' => null,
        'is_approved' => true,
    ]);

    actingAs($user)
        ->post(route('register.whatsapp.send'), [
            'whatsapp' => '+62 812-3456-789',
        ])
        ->assertSessionHasErrors('whatsapp');

    expect($user->fresh()?->whatsapp)->toBeNull();
    Notification::assertNothingSent();
});

it('shows a friendly error when whatsapp otp delivery cannot reach the gateway', function () {
    $gateway = mock(WhatsAppGateway::class);
    $gateway->shouldReceive('configured')->andReturn(true);
    $gateway->shouldReceive('sendMessage')->andThrow(new RuntimeException('request invalid on disconnected device'));
    app()->instance(WhatsAppGateway::class, $gateway);

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'whatsapp' => '08123456789',
        'address' => null,
        'profile_completed_at' => null,
        'whatsapp_verified_at' => null,
        'is_approved' => true,
    ]);

    actingAs($user)
        ->post(route('register.whatsapp.send'))
        ->assertSessionHasErrors([
            'otp' => 'Kode belum dapat dikirim. Coba lagi beberapa saat lagi.',
        ]);

    expect(Cache::has('whatsapp-otp:challenge:'.$user->id))->toBeFalse()
        ->and(RateLimiter::attempts('whatsapp-otp:hourly:'.$user->id))->toBe(0);
});
