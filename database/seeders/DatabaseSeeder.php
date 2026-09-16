<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seeder penting (peran, pengaturan, izin, super admin) dijalankan di semua
     * environment agar `db:seed` di produksi tetap menyiapkan data esensial yang
     * dibutuhkan panel admin. Data contoh hanya dijalankan di local/development.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AppSettingSeeder::class,
            ShieldSeeder::class,
            SuperAdminSeeder::class,
        ]);

        if (app()->environment('local', 'development')) {
            $this->call(LocalDevelopmentSeeder::class);
        }
    }
}
