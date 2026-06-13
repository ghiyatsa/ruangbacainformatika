<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'summary' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'cover_image' => null,
            'is_published' => false,
            'published_at' => null,
            'view_count' => $this->faker->numberBetween(0, 100),
            'status' => Post::STATUS_DRAFT,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Set the post as published (approved).
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Post::STATUS_APPROVED,
            'is_published' => true,
            'published_at' => now()->subDays(rand(0, 30)),
            'reviewed_by_user_id' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Set the post as pending review.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Post::STATUS_PENDING,
        ]);
    }
}
