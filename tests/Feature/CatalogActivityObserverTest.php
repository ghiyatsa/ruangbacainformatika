<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

function makeCatalogActivitySuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('logs catalog mutations created by admins', function () {
    $admin = makeCatalogActivitySuperAdmin();

    actingAs($admin);

    $book = Book::factory()->create([
        'title' => 'Audit Buku Admin',
    ]);

    $log = ActivityLog::query()
        ->where('action', 'catalog.buku.created')
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log?->user_id)->toBe($admin->getKey())
        ->and($log?->subject_id)->toBe($book->getKey())
        ->and($log?->subject_label)->toBe('Audit Buku Admin');
});
