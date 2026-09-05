<?php

namespace Database\Factories;

use App\Models\InternshipReportSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternshipReportSubmission>
 */
class InternshipReportSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(6),
            'company_name' => $this->faker->company(),
            'company_address' => $this->faker->address(),
            'academic_advisor' => $this->faker->name(),
            'field_advisor' => $this->faker->name(),
            'year' => (int) $this->faker->year(),
            'abstract' => $this->faker->paragraphs(2, true),
            'keywords' => implode(', ', $this->faker->words(4)),
            'report_file_path' => 'internship-reports/submissions/fake_report.pdf',
            'status' => InternshipReportSubmission::STATUS_PENDING,
        ];
    }
}
