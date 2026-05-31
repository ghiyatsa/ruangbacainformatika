<?php

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Spatie\Permission\Models\Role;

it('seeds production-safe bootstrap data without creating a placeholder super admin', function () {
    config()->set('app.super_admin.email', 'admin@example.com');

    app(ProductionSeeder::class)->run();

    expect(Role::query()->pluck('name')->sort()->values()->all())
        ->toBe(['member', 'staff', 'super_admin'])
        ->and(Setting::query()->where('section', 'general')->where('key', 'site_name')->exists())
        ->toBeTrue()
        ->and(Category::query()->count())
        ->toBe(7)
        ->and(User::query()->exists())
        ->toBeFalse();
});

it('creates the initial super admin when production config is provided', function () {
    config()->set('app.super_admin.name', 'Admin Produksi');
    config()->set('app.super_admin.email', 'admin@unimal.ac.id');
    config()->set('app.super_admin.whatsapp', '628123456789');
    config()->set('app.super_admin.address', 'Kampus Bukit Indah');

    app(ProductionSeeder::class)->run();

    $user = User::query()->where('email', 'admin@unimal.ac.id')->first();

    expect($user)->not->toBeNull()
        ->and($user?->name)->toBe('Admin Produksi')
        ->and($user?->auth_provider)->toBe('google')
        ->and($user?->is_approved)->toBeTrue()
        ->and($user?->hasRole('super_admin'))->toBeTrue();
});
