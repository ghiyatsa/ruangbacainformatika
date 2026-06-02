<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Permission::query()->exists()) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        Artisan::call('shield:generate --all --panel=admin --ignore-existing-policies --no-interaction --silent');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
