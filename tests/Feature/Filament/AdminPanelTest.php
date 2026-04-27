<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

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

test('guests are redirected away from the admin panel', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('non admin users can not access the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('super admin users can access the admin dashboard', function () {
    $user = makeSuperAdmin();

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk();
});

test('super admin users can access the admin users resource', function () {
    $user = makeSuperAdmin();

    $this->actingAs($user)
        ->get('/admin/users')
        ->assertOk();
});

test('super admin users can render the general settings form', function () {
    $user = makeSuperAdmin();

    $this->actingAs($user)
        ->get(route('filament.admin.settings.pages.general-settings'))
        ->assertOk()
        ->assertSee('Nama Situs')
        ->assertSee('WhatsApp Bantuan')
        ->assertSee('Maksimal Buku Dipinjam')
        ->assertSee('Durasi Peminjaman (Hari Kerja)')
        ->assertSee('Simpan');
});

test('super admin users are redirected from settings cluster to the first settings page', function () {
    $user = makeSuperAdmin();

    $this->actingAs($user)
        ->get('/admin/settings')
        ->assertRedirect(route('filament.admin.settings.pages.general-settings', absolute: false));
});

test('super admin users can access key admin resources', function (string $path) {
    $user = makeSuperAdmin();

    $this->actingAs($user)
        ->get($path)
        ->assertOk();
})->with([
    '/admin/books',
    '/admin/authors',
    '/admin/categories',
    '/admin/publishers',
    '/admin/loans',
    '/admin/visit-logs',
    '/admin/kiosk-devices',
    '/admin/settings/general-settings',
]);
