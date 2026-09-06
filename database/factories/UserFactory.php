<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->numerify('230170###').'@mhs.unimal.ac.id',
            'remember_token' => Str::random(10),
            'auth_provider' => 'google',
            'google_id' => null,
            'whatsapp' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'profile_completed_at' => now(),
            'is_approved' => true,
        ];
    }

    public function member(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
            $user->assignRole($role);
        });
    }

    public function staff(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
            $user->assignRole($role);
        });
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            $user->assignRole($role);
        });
    }
}
