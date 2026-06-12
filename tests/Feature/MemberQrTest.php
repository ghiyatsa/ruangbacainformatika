<?php

use App\Models\User;
use App\Services\KioskBorrowVerificationService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

it('members can open the member qr page', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    actingAs($member)
        ->get(route('settings.member-qr.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/member-qr')
            ->where('memberQr.hasActiveQr', false)
            ->where('memberQr.qrCodeSvg', null)
        );
});

it('members can generate a member qr from their account page', function () {
    withoutMiddleware(PreventRequestForgery::class);

    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $member = User::factory()->create([
        'whatsapp_verified_at' => now(),
    ]);
    $member->assignRole('member');

    actingAs($member)
        ->post(route('settings.member-qr.generate'))
        ->assertRedirect(route('settings.member-qr.show'))
        ->assertSessionHas(
            'inertia.flash_data.toast.message',
            'QR anggota berhasil dibuat.',
        );

    expect(session('member_qr.payload'))->toBeString();

    $summary = app(KioskBorrowVerificationService::class)->current($member);

    expect($summary)->not->toBeNull();

    actingAs($member)
        ->get(route('settings.member-qr.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/member-qr')
            ->where('memberQr.hasActiveQr', true)
            ->where('memberQr.qrCodeSvg', fn (?string $value): bool => filled($value))
            ->where('memberQr.expiresAtIso', fn (?string $value): bool => filled($value))
        );
});
