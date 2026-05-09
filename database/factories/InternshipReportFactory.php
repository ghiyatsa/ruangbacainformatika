<?php

namespace Database\Factories;

use App\Models\InternshipReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternshipReport>
 */
class InternshipReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'author_name' => $this->faker->name(),
            'student_id' => $this->faker->numerify('##########'),
            'year' => $this->faker->year(),
            'abstract' => $this->faker->paragraphs(3, true),
            'keywords' => implode(', ', $this->faker->words(5)),
        ];
    }
}
