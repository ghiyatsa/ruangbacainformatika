<?php

namespace Database\Factories;

use App\Models\SimilaritySyncStatus;
use App\Models\Skripsi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SimilaritySyncStatus>
 */
class SimilaritySyncStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'syncable_id' => fake()->unique()->numberBetween(1, 999999),
            'syncable_type' => Skripsi::class,
            'status' => SimilaritySyncStatus::STATUS_PENDING,
            'last_operation' => SimilaritySyncStatus::OPERATION_UPSERT,
            'attempts' => 0,
            'last_attempt_at' => null,
            'last_synced_at' => null,
            'last_error' => null,
        ];
    }
}
