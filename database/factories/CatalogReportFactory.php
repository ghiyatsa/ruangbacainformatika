<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\CatalogReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogReport>
 */
class CatalogReportFactory extends Factory
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
            'catalog_type' => CatalogReport::CATALOG_TYPE_BOOK,
            'reportable_type' => Book::class,
            'reportable_id' => Book::factory(),
            'catalog_title' => fake()->sentence(4),
            'catalog_url' => fake()->url(),
            'reporter_name' => fake()->name(),
            'reporter_email' => fake()->safeEmail(),
            'message' => fake()->paragraph(),
            'status' => CatalogReport::STATUS_PENDING,
            'admin_notes' => null,
            'reviewed_at' => null,
        ];
    }
}
