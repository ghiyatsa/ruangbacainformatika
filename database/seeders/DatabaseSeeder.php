<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'development')) {
            return;
        }

        $this->call([
            RoleSeeder::class,
            AppSettingSeeder::class,
            ShieldSeeder::class,
            SuperAdminSeeder::class,
            LocalDevelopmentSeeder::class,
        ]);
    }
}
