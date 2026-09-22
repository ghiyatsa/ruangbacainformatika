<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostRevision>
 */
class PostRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'status' => PostRevision::STATUS_PENDING,
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(3),
            'summary' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'cover_image' => null,
            'allow_comments' => true,
            'categories' => [],
            'tags' => [],
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostRevision::STATUS_DRAFT,
        ]);
    }
}
