<?php

use App\Models\Loan;
use App\Models\Setting;
use App\Models\User;
use App\Services\KioskLoanService;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

function makeEligibleBorrower(): User
{
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'whatsapp' => '08123456789',
        'whatsapp_verified_at' => now(),
        'address' => 'Jl. Kampus',
        'is_approved' => true,
    ]);
    $user->assignRole('member');

    return $user;
}

it('reports a blocking reason for non campus emails', function () {
    $user = User::factory()->create([
        'email' => 'someone@gmail.com',
        'is_approved' => true,
    ]);

    expect($user->borrowingBlockReason())->toBe([
        'title' => 'Email bukan domain kampus',
        'message' => 'Peminjaman buku hanya untuk pengguna dengan email @mhs.unimal.ac.id atau @unimal.ac.id.',
        'actionUrl' => null,
    ])
        ->and($user->canStartLoanRequest())->toBeFalse();
});

it('reports approval as the blocking reason for unapproved campus users', function () {
    $user = User::factory()->create([
        'email' => 'dosen@unimal.ac.id',
        'is_approved' => false,
    ]);

    expect($user->borrowingBlockReason()['title'])->toBe('Menunggu persetujuan admin')
        ->and($user->canStartLoanRequest())->toBeFalse();
});

it('points unverified users to whatsapp verification', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'is_approved' => true,
        'whatsapp_verified_at' => null,
    ]);

    $reason = $user->borrowingBlockReason();

    expect($reason['title'])->toBe('Verifikasi WhatsApp')
        ->and($reason['actionUrl'])->toBe(route('register.whatsapp', absolute: false));
});

it('reports missing member role for eligible users without the role', function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'is_approved' => true,
        'whatsapp_verified_at' => now(),
    ]);
    $user->assignRole('staff');
    $user->removeRole('member');

    expect($user->hasRole('member'))->toBeFalse()
        ->and($user->borrowingBlockReason()['title'])->toBe('Peran anggota belum aktif')
        ->and($user->canStartLoanRequest())->toBeFalse();
});

it('reports incomplete profile at checkout but not when adding to cart', function () {
    $user = makeEligibleBorrower();
    $user->forceFill(['address' => null])->save();

    expect($user->borrowingBlockReason())->toBeNull()
        ->and($user->borrowingBlockReason(true)['title'])->toBe('Profil belum lengkap')
        ->and($user->borrowingBlockReason(true)['actionUrl'])->toBe(route('settings.profile.edit', absolute: false))
        ->and($user->canStartLoanRequest())->toBeFalse();
});

it('reports temporary restriction for overdue borrowers only at checkout', function () {
    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_suspension_enabled'],
        ['value' => '1'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'library', 'key' => 'late_return_suspend_after_days'],
        ['value' => '1'],
    );

    $user = makeEligibleBorrower();

    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'due_at' => now()->subDays(3),
    ]);

    expect($user->borrowingBlockReason())->toBeNull()
        ->and($user->borrowingBlockReason(true)['title'])->toBe('Peminjaman dibatasi sementara')
        ->and($user->borrowingBlockReason(true)['message'])->toContain('terlambat')
        ->and($user->canStartLoanRequest())->toBeFalse();
});

it('returns no blocking reason for eligible borrowers', function () {
    $user = makeEligibleBorrower();

    expect($user->borrowingBlockReason())->toBeNull()
        ->and($user->borrowingBlockReason(true))->toBeNull()
        ->and($user->canStartLoanRequest())->toBeTrue();
});

it('kiosk borrow surfaces the blocking reason', function () {
    $user = User::factory()->create([
        'email' => 'someone@gmail.com',
        'is_approved' => true,
    ]);

    try {
        app(KioskLoanService::class)->borrow($user->email, [999999]);
    } catch (ValidationException $exception) {
        expect($exception->errors()['member_identifier'][0])
            ->toContain('Peminjaman buku hanya untuk pengguna dengan email @mhs.unimal.ac.id atau @unimal.ac.id');

        return;
    }

    $this->fail('Expected ValidationException to be thrown.');
});
