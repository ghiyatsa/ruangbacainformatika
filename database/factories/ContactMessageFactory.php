<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
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
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->numerify('08##########'),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraphs(2, true),
            'status' => ContactMessage::STATUS_NEW,
            'admin_notes' => null,
            'reviewed_at' => null,
        ];
    }
}
