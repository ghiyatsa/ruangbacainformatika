<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookItem>
 */
class BookItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'internal_code' => fake()->unique()->bothify('??-###'),
            'status' => 'available',
            'condition' => 'good',
        ];
    }

    public function available(): static
    {
        return $this->state(['status' => 'available']);
    }

    public function borrowed(): static
    {
        return $this->state(['status' => 'borrowed']);
    }
}
