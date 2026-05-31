<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AppSettingSeeder::class,
            ShieldSeeder::class,
            CategorySeeder::class,
        ]);

        if (! $this->shouldSeedSuperAdmin()) {
            $this->command?->warn('Super admin seed dilewati. Atur APP_SUPER_ADMIN_EMAIL dengan email production yang valid untuk membuat akun admin awal.');

            return;
        }

        $this->call([
            SuperAdminSeeder::class,
        ]);
    }

    protected function shouldSeedSuperAdmin(): bool
    {
        $email = Str::lower(trim((string) config('app.super_admin.email')));

        if ($email === '' || $email === 'admin@example.com') {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
