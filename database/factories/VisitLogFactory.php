<?php

namespace Database\Factories;

use App\Models\VisitLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitLog>
 */
class VisitLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'visitor_type' => fake()->randomElement(array_keys(VisitLog::visitorTypeOptions())),
            'identity_number' => fake()->optional()->numerify('##########'),
            'institution' => fake()->optional()->company(),
            'phone' => fake()->optional()->numerify('08##########'),
            'purpose' => fake()->randomElement(array_keys(VisitLog::purposeOptions())),
            'notes' => fake()->optional()->sentence(),
            'visited_at' => now(),
        ];
    }
}
