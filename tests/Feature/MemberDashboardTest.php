<?php

use App\Models\DocumentSubmission;
use App\Models\User;
use App\Services\Auth\AuthenticationRedirector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['super_admin', 'staff', 'member'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

/**
 * Anggota dengan onboarding tuntas: email kampus, disetujui, dan
 * WhatsApp sudah terverifikasi sehingga tidak diarahkan ke tahap awal.
 */
function memberUser(): User
{
    $user = User::factory()->create([
        'email' => 'anggota'.fake()->unique()->numerify('####').'@mhs.unimal.ac.id',
        'profile_completed_at' => now(),
        'whatsapp_verified_at' => now(),
        'is_approved' => true,
    ]);
    $user->syncRoles(['member']);
    $user->unsetRelation('roles');

    return $user;
}

function defaultPathFor(User $user): string
{
    $redirector = app(AuthenticationRedirector::class);
    $method = new ReflectionMethod($redirector, 'defaultPathFor');
    $method->setAccessible(true);

    return $method->invoke($redirector, $user);
}

it('menampilkan dasbor anggota dengan ringkasan pengajuan', function () {
    $user = memberUser();

    DocumentSubmission::factory()->create([
        'user_id' => $user->id,
        'status' => DocumentSubmission::STATUS_PENDING,
    ]);
    DocumentSubmission::factory()->create([
        'user_id' => $user->id,
        'status' => DocumentSubmission::STATUS_APPROVED,
        'reviewed_at' => now(),
    ]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('member-dashboard/index')
            ->where('counts.total', 2)
            ->where('counts.pending', 1)
            ->where('counts.approved', 1)
            ->has('submissions', 2)
        );
});

it('hanya menampilkan pengajuan milik anggota yang sedang masuk', function () {
    $user = memberUser();
    $lain = memberUser();

    DocumentSubmission::factory()->create([
        'user_id' => $user->id,
        'status' => DocumentSubmission::STATUS_PENDING,
    ]);
    DocumentSubmission::factory()->create([
        'user_id' => $lain->id,
        'status' => DocumentSubmission::STATUS_PENDING,
    ]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('counts.total', 1)
            ->has('submissions', 1)
        );
});

it('menampilkan dasbor kosong bila belum ada pengajuan', function () {
    $user = memberUser();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('counts.total', 0)
            ->has('submissions', 0)
        );
});

it('menolak tamu mengakses dasbor anggota', function () {
    get(route('dashboard'))->assertRedirect(route('login'));
});

it('mengarahkan anggota ke dasbor setelah login', function () {
    expect(Route::has('dashboard'))->toBeTrue();

    expect(defaultPathFor(memberUser()))->toBe(route('dashboard', absolute: false));
});

it('tidak mengarahkan pengelola ke dasbor anggota', function () {
    $admin = User::factory()->create([
        'email' => 'staff@unimal.ac.id',
        'profile_completed_at' => now(),
        'whatsapp_verified_at' => now(),
        'is_approved' => true,
    ]);
    $admin->syncRoles(['staff']);
    $admin->unsetRelation('roles');

    expect(defaultPathFor($admin))->toBe(route('home', absolute: false));
});
