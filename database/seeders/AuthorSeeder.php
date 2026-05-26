<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = [
            'Robert C. Martin',
            'Martin Fowler',
            'Taylor Otwell',
            'Andrew S. Tanenbaum',
            'Abraham Silberschatz',
            'Donald Knuth',
            'Eric Evans',
            'Thomas H. Cormen',
            'Andrea Hirata',
            'Rhenald Kasali',
        ];

        foreach ($authors as $authorName) {
            Author::query()->firstOrCreate(
                ['name' => $authorName],
                ['slug' => Str::slug($authorName)]
            );
        }
    }
}
