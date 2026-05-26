<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = [
            'O\'Reilly Media',
            'Packt Publishing',
            'Andi Offset',
            'Informatika Bandung',
            'Elex Media Komputindo',
            'Pearson Education',
            'Addison-Wesley',
            'Manning Publications',
        ];

        foreach ($publishers as $publisherName) {
            Publisher::query()->firstOrCreate(
                ['name' => $publisherName],
                ['slug' => Str::slug($publisherName)]
            );
        }
    }
}
