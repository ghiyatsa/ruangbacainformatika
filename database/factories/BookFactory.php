<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => ucwords($title),
            'slug' => Str::slug($title),
            'isbn' => fake()->unique()->numerify('978########'),
            'description' => fake()->paragraph(),
            'published_year' => fake()->year(),
            'pages' => fake()->numberBetween(100, 800),
            'language' => 'Indonesia',
            'is_featured' => false,
            'is_published' => true,
            'view_count' => 0,
            'publisher_id' => Publisher::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true, 'is_published' => true]);
    }
}
