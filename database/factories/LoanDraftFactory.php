<?php

namespace Database\Factories;

use App\Models\LoanDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LoanDraft>
 */
class LoanDraftFactory extends Factory
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
            'status' => LoanDraft::STATUS_PENDING,
            'token_hash' => null,
            'expires_at' => null,
            'consumed_at' => null,
        ];
    }

    public function readyForScan(): static
    {
        return $this->state(fn (): array => [
            'token_hash' => hash('sha256', Str::random(48)),
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'token_hash' => hash('sha256', Str::random(48)),
            'expires_at' => now()->subMinute(),
            'status' => LoanDraft::STATUS_EXPIRED,
        ]);
    }
}
