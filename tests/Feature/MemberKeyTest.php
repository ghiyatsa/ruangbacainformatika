<?php

use App\Models\User;
use App\Services\KioskBorrowVerificationService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

it('members can view the member key on their profile page', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    actingAs($member)
        ->get(route('settings.member-key.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/member-key')
            ->where('memberKey.hasActiveQr', true)
            ->where('memberKey.qrCodeSvg', fn (?string $value): bool => filled($value))
            ->where('memberKey.expiresAtIso', fn (?string $value): bool => filled($value))
        );
});

it('members can generate a member key from their account page', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    actingAs($member)
        ->post(route('settings.member-key.generate'))
        ->assertRedirect(route('settings.member-key.show'))
        ->assertSessionHas(
            'inertia.flash_data.toast.message',
            'Member key berhasil diperbarui.',
        );

    $summary = app(KioskBorrowVerificationService::class)->current($member);

    expect($summary)->not->toBeNull()
        ->toHaveKey('qrCodeSvg')
        ->toHaveKey('expiresAtIso');

    actingAs($member)
        ->get(route('settings.member-key.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/member-key')
            ->where('memberKey.hasActiveQr', true)
            ->where('memberKey.qrCodeSvg', fn (?string $value): bool => filled($value))
            ->where('memberKey.expiresAtIso', fn (?string $value): bool => filled($value))
        );
});

it('member key expires after one minute', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    $service = app(KioskBorrowVerificationService::class);

    $verification = $service->generate($member);

    expect($verification['expires_at']->diffInSeconds(now()))->toBeLessThanOrEqual(60);

    Carbon::setTestNow(now()->addSeconds(61));

    expect($service->current($member))->toBeNull();

    Carbon::setTestNow();
});
