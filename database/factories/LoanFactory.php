<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
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
            'status' => Loan::STATUS_BORROWED,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(7),
            'returned_at' => null,
            'reminder_sent_at' => null,
        ];
    }
}
