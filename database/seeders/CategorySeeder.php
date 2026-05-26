<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kecerdasan Buatan',
                'description' => 'Buku dan literatur mengenai Kecerdasan Buatan (AI), Machine Learning, Deep Learning, dan Sistem Pakar.',
            ],
            [
                'name' => 'Jaringan Komputer',
                'description' => 'Koleksi mengenai infrastruktur jaringan, administrasi server, protokol, Cisco, dan keamanan siber.',
            ],
            [
                'name' => 'Rekayasa Perangkat Lunak',
                'description' => 'Materi seputar metodologi pengembangan perangkat lunak (Agile, Scrum), desain pola (Design Patterns), dan arsitektur sistem.',
            ],
            [
                'name' => 'Sistem Informasi',
                'description' => 'Koleksi mengenai analisis sistem, manajemen proyek IT, audit sistem informasi, dan e-commerce.',
            ],
            [
                'name' => 'Sains Data',
                'description' => 'Buku seputar pengolahan data besar (Big Data), visualisasi data, analisis statistik, dan pemrograman R/Python.',
            ],
            [
                'name' => 'Pemrograman Web & Mobile',
                'description' => 'Panduan pengembangan aplikasi menggunakan framework modern seperti Laravel, React, Flutter, Android Studio, dll.',
            ],
            [
                'name' => 'Sistem Basis Data',
                'description' => 'Koleksi mengenai desain basis data relasional (SQL) maupun non-relasional (NoSQL/MongoDB).',
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }
    }
}
