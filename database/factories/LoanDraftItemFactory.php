<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\LoanDraft;
use App\Models\LoanDraftItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanDraftItem>
 */
class LoanDraftItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loan_draft_id' => LoanDraft::factory(),
            'book_id' => Book::factory(),
        ];
    }
}
