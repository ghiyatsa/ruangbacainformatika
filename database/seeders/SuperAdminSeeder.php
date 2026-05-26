<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $name = (string) config('app.super_admin.name');
        $email = Str::lower((string) config('app.super_admin.email'));
        $whatsapp = (string) config('app.super_admin.whatsapp');
        $address = (string) config('app.super_admin.address');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'auth_provider' => 'google',
                'is_approved' => true,
                'whatsapp' => filled($whatsapp) ? $whatsapp : null,
                'whatsapp_verified_at' => now(),
                'address' => filled($address) ? $address : null,
                'profile_completed_at' => now(),
            ]
        );

        $user->assignRole($role);
    }
}
