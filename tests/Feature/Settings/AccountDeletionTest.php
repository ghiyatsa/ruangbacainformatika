<?php

use App\Models\Loan;
use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
});

it('shows the account deletion section with the confirmation phrase', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('settings.profile.edit'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('settings/profile')
                ->where('accountDeletion.confirmationPhrase', AccountDeletionService::CONFIRMATION_PHRASE)
                ->where('accountDeletion.gracePeriodDays', AccountDeletionService::GRACE_PERIOD_DAYS),
        );
});

it('deletes the account, anonymises personal data and keeps loan history', function () {
    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'whatsapp' => '081234567890',
        'address' => 'Jl. Uji No. 1',
        'google_id' => 'google-abc',
    ]);

    // Dua pinjaman yang sudah dikembalikan: riwayat harus tetap ada.
    Loan::factory()->count(2)->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_RETURNED,
        'returned_at' => now()->subDay(),
        'due_at' => now()->subDays(2),
    ]);

    $userId = $user->id;
    $loansBefore = Loan::query()->where('user_id', $userId)->count();

    actingAs($user)
        ->delete(route('settings.profile.destroy'), [
            'confirmation' => AccountDeletionService::CONFIRMATION_PHRASE,
        ])
        ->assertRedirect('/');

    $trashed = User::withTrashed()->find($userId);

    // Data pribadi sudah dianonimkan.
    expect($trashed->name)->not->toBe('Budi Santoso')
        ->and($trashed->email)->toContain('@anon.invalid')
        ->and($trashed->whatsapp)->toBeNull()
        ->and($trashed->address)->toBeNull()
        ->and($trashed->google_id)->toBeNull()
        ->and($trashed->deleted_at)->not->toBeNull();

    // Akun tidak terlihat lagi pada query biasa.
    expect(User::find($userId))->toBeNull();

    // Riwayat sirkulasi TIDAK boleh hilang.
    expect(Loan::query()->where('user_id', $userId)->count())->toBe($loansBefore);
});

it('refuses deletion while an active loan exists', function () {
    $user = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'returned_at' => null,
        'due_at' => now()->addDays(7),
    ]);

    actingAs($user)
        ->delete(route('settings.profile.destroy'), [
            'confirmation' => AccountDeletionService::CONFIRMATION_PHRASE,
        ])
        ->assertSessionHasErrors('deletion');

    expect(User::find($user->id))->not->toBeNull();
});

it('refuses deletion for an overdue loan that is not yet returned', function () {
    $user = User::factory()->create();
    Loan::factory()->create([
        'user_id' => $user->id,
        'status' => Loan::STATUS_BORROWED,
        'returned_at' => null,
        'due_at' => now()->subDays(30),
    ]);

    actingAs($user)
        ->delete(route('settings.profile.destroy'), [
            'confirmation' => AccountDeletionService::CONFIRMATION_PHRASE,
        ])
        ->assertSessionHasErrors('deletion');

    expect(User::find($user->id))->not->toBeNull();
});

it('refuses deletion when the confirmation phrase is wrong', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->delete(route('settings.profile.destroy'), [
            'confirmation' => 'hapus saja',
        ])
        ->assertSessionHasErrors('confirmation');

    expect(User::find($user->id))->not->toBeNull();
});

it('refuses deletion when the confirmation phrase is missing', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->delete(route('settings.profile.destroy'), [])
        ->assertSessionHasErrors('confirmation');

    expect(User::find($user->id))->not->toBeNull();
});

it('refuses deletion for an administrative account', function () {
    Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('super_admin');

    actingAs($user)
        ->delete(route('settings.profile.destroy'), [
            'confirmation' => AccountDeletionService::CONFIRMATION_PHRASE,
        ])
        ->assertSessionHasErrors('deletion');

    expect(User::find($user->id))->not->toBeNull();
});

it('revokes roles when the account is deleted', function () {
    $user = User::factory()->create();

    actingAs($user)->delete(route('settings.profile.destroy'), [
        'confirmation' => AccountDeletionService::CONFIRMATION_PHRASE,
    ]);

    $trashed = User::withTrashed()->find($user->id);

    expect($trashed->getRoleNames()->count())->toBe(0);
});

it('records the deletion request time and reason', function () {
    $user = User::factory()->create();

    actingAs($user)->delete(route('settings.profile.destroy'), [
        'confirmation' => AccountDeletionService::CONFIRMATION_PHRASE,
        'reason' => 'Tidak lagi meminjam buku',
    ]);

    $trashed = User::withTrashed()->find($user->id);

    expect($trashed->deletion_requested_at)->not->toBeNull()
        ->and($trashed->deletion_reason)->toBe('Tidak lagi meminjam buku');
});

it('blocks the account deletion route for guests', function () {
    $this->delete(route('settings.profile.destroy'), [
        'confirmation' => AccountDeletionService::CONFIRMATION_PHRASE,
    ])->assertRedirect(route('login'));
});

it('restores the account when the deletion is cancelled during grace period', function () {
    $user = User::factory()->create();

    actingAs($user)->delete(route('settings.profile.destroy'), [
        'confirmation' => AccountDeletionService::CONFIRMATION_PHRASE,
    ]);

    $trashed = User::withTrashed()->find($user->id);
    expect($trashed->trashed())->toBeTrue();

    app(AccountDeletionService::class)->cancel($trashed);

    $restored = User::find($user->id);
    expect($restored)->not->toBeNull()
        ->and($restored->deletion_requested_at)->toBeNull();
});
