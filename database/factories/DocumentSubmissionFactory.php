<?php

namespace Database\Factories;

use App\Models\DocumentSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentSubmission>
 */
class DocumentSubmissionFactory extends Factory
{
    protected $model = DocumentSubmission::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => DocumentSubmission::TYPE_INTERNSHIP_REPORT,
            'status' => DocumentSubmission::STATUS_PENDING,
            'title' => fake()->sentence(6),
            'year' => fake()->numberBetween(2020, 2026),
            'abstract' => fake()->paragraph(3),
            'keywords' => 'laravel, react, ai',
            'academic_advisor' => fake()->name(),
            'company_name' => fake()->company(),
            'company_address' => fake()->address(),
            'field_advisor' => fake()->name(),
            'document_file_path' => 'internship-reports/submissions/test.pdf',
            'endorsement_file_path' => 'internship-reports/endorsements/test.pdf',
        ];
    }

    public function skripsi(): static
    {
        return $this->state(fn () => [
            'type' => DocumentSubmission::TYPE_SKRIPSI,
            'company_name' => null,
            'company_address' => null,
            'field_advisor' => null,
        ]);
    }

    public function bookDonation(): static
    {
        return $this->state(fn () => [
            'type' => DocumentSubmission::TYPE_BOOK_DONATION,
            'author_names' => fake()->name(),
            'publisher_name' => fake()->company(),
            'isbn' => fake()->isbn13(),
            'edition' => 'Edisi 1',
            'pages' => '250',
            'book_condition' => 'good',
            'abstract' => null,
            'academic_advisor' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => DocumentSubmission::STATUS_APPROVED,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'receipt_number' => sprintf('DOC/RB-IF/%s/%03d', now()->format('Y/m'), fake()->numberBetween(1, 999)),
        ]);
    }
}
