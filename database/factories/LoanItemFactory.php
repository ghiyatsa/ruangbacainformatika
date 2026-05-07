<?php

namespace Database\Factories;

use App\Models\BookItem;
use App\Models\Loan;
use App\Models\LoanItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanItem>
 */
class LoanItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory(),
            'book_item_id' => BookItem::factory(),
            'returned_at' => null,
        ];
    }
}
