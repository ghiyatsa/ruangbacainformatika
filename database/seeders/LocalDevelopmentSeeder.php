<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LocalDevelopmentSeeder extends Seeder
{
    /**
     * Seed sample content for local and development environments only.
     */
    public function run(): void
    {
        $this->call([
            AuthorSeeder::class,
            PublisherSeeder::class,
            BookSeeder::class,
            SkripsiSeeder::class,
            ThesisSeeder::class,
            InternshipReportSeeder::class,
        ]);
    }
}
