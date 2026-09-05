<?php

use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
});

it('redirects guest to login when accessing member guarded routes', function (string $route) {
    get($route)->assertRedirect(route('login'));
})->with([
    '/skripsi',
    '/skripsi/1234567890',
    '/thesis',
    '/thesis/1234567890',
    '/internship-reports',
    '/internship-reports/1234567890',
]);

it('aborts with 403 when user is not a member', function (string $route) {
    Skripsi::factory()->create(['student_id' => '1234567890']);
    Thesis::factory()->create(['student_id' => '1234567890']);
    InternshipReport::factory()->create(['student_id' => '1234567890']);

    $user = User::factory()->create([
        'email' => 'guest@gmail.com',
        'is_approved' => false,
        'profile_completed_at' => now(),
    ]);

    actingAs($user)
        ->get($route)
        ->assertForbidden();
})->with([
    '/skripsi',
    '/skripsi/1234567890',
    '/thesis',
    '/thesis/1234567890',
    '/internship-reports',
    '/internship-reports/1234567890',
]);

it('allows member with completed profile to access guarded routes', function () {
    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'is_approved' => true,
        'profile_completed_at' => now(),
    ]);
    $user->assignRole('member');

    actingAs($user)
        ->get(route('skripsi.index'))
        ->assertOk();
});
