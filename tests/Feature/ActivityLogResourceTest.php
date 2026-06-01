<?php

use App\Models\ActivityLog;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function makeActivityLogSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('super admin users can access the activity log resource', function () {
    $user = makeActivityLogSuperAdmin();

    ActivityLog::query()->create([
        'action' => 'settings.general.updated',
        'description' => 'Pengaturan umum diperbarui',
        'subject_label' => 'Pengaturan umum',
        'properties' => ['changes' => ['site_name' => ['before' => 'Lama', 'after' => 'Baru']]],
    ]);

    actingAs($user)
        ->get('/admin/activity-logs')
        ->assertOk()
        ->assertSee('Log Aktivitas')
        ->assertSee('Kode')
        ->assertSee('Pengaturan umum diperbarui');
});

it('non admin users cannot access the activity log resource', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/admin/activity-logs')
        ->assertForbidden();
});
