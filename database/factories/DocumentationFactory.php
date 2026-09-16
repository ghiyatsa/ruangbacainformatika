<?php

namespace Database\Factories;

use App\Models\Documentation;
use App\Models\DocumentationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Documentation>
 */
class DocumentationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'documentation_category_id' => DocumentationCategory::factory(),
            'title' => fake()->unique()->sentence(4),
            'summary' => fake()->sentence(),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
