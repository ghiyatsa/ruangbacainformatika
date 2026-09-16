<?php

namespace Database\Factories;

use App\Models\DocumentationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentationCategory>
 */
class DocumentationCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
