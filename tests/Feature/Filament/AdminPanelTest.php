<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function makeSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('guests are redirected away from the admin panel', function () {
    get('/admin')->assertRedirect(route('login'));
});

it('non admin users can not access the admin panel', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('super admin users can access the admin dashboard', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin')
        ->assertOk();
});

it('super admin users can access the admin users resource', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/users')
        ->assertOk();
});

it('super admin users can render the general settings form', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get(route('filament.admin.settings.pages.general-settings'))
        ->assertOk()
        ->assertSee('Nama Situs')
        ->assertSee('Slogan')
        ->assertSee('WhatsApp Bantuan')
        ->assertSee('Simpan');
});

it('super admin users can render the library settings form', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get(route('filament.admin.settings.pages.library'))
        ->assertOk()
        ->assertSee('Maksimal Buku Dipinjam')
        ->assertSee('Durasi Peminjaman (Hari Kerja)')
        ->assertSee('Simpan');
});


it('super admin users are redirected from settings cluster to the first settings page', function () {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get('/admin/settings')
        ->assertRedirect(route('filament.admin.settings.pages.general-settings', absolute: false));
});

it('super admin users can access key admin resources', function (string $path) {
    $user = makeSuperAdmin();

    actingAs($user)
        ->get($path)
        ->assertOk();
})->with([
    '/admin/books',
    '/admin/authors',
    '/admin/categories',
    '/admin/publishers',
    '/admin/loans',
    '/admin/visit-logs',
    '/admin/settings/kiosk',
    '/admin/settings/general-settings',
]);
