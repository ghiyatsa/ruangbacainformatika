<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
}
