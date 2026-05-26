<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookItem;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $booksData = [
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'isbn' => '9780132350884',
                'description' => 'Buku panduan legendaris tentang penulisan kode yang bersih, rapi, dan mudah dipelihara. Sangat direkomendasikan untuk seluruh mahasiswa Teknik Informatika.',
                'published_year' => 2008,
                'pages' => 464,
                'language' => 'English',
                'is_featured' => true,
                'is_borrowable' => true,
                'publisher_name' => 'Pearson Education',
                'author_names' => ['Robert C. Martin'],
                'category_names' => ['Rekayasa Perangkat Lunak'],
            ],
            [
                'title' => 'Refactoring: Improving the Design of Existing Code',
                'isbn' => '9780134757599',
                'description' => 'Buku klasik karya Martin Fowler tentang teknik restrukturisasi kode yang sudah ada tanpa mengubah perilaku eksternalnya untuk meningkatkan kualitas desain perangkat lunak.',
                'published_year' => 2018,
                'pages' => 448,
                'language' => 'English',
                'is_featured' => true,
                'is_borrowable' => true,
                'publisher_name' => 'Addison-Wesley',
                'author_names' => ['Martin Fowler'],
                'category_names' => ['Rekayasa Perangkat Lunak'],
            ],
            [
                'title' => 'Laravel Up & Running: A Framework for Efficient PHP Development',
                'isbn' => '9781492041214',
                'description' => 'Panduan praktis dan mendalam untuk mempelajari framework PHP Laravel, mulai dari routing, database, testing, hingga deployment.',
                'published_year' => 2019,
                'pages' => 538,
                'language' => 'English',
                'is_featured' => true,
                'is_borrowable' => true,
                'publisher_name' => 'O\'Reilly Media',
                'author_names' => ['Taylor Otwell'],
                'category_names' => ['Pemrograman Web & Mobile'],
            ],
            [
                'title' => 'Modern Operating Systems',
                'isbn' => '9780133591620',
                'description' => 'Referensi utama kuliah Sistem Operasi yang membahas manajemen proses, memori, sistem berkas, virtualisasi, dan keamanan sistem komputer modern.',
                'published_year' => 2014,
                'pages' => 1136,
                'language' => 'English',
                'is_featured' => false,
                'is_borrowable' => true,
                'publisher_name' => 'Pearson Education',
                'author_names' => ['Andrew S. Tanenbaum'],
                'category_names' => ['Jaringan Komputer'],
            ],
            [
                'title' => 'Database System Concepts',
                'isbn' => '9780073523323',
                'description' => 'Buku dasar-dasar perancangan, query (SQL), transaksi, arsitektur penyimpanan, dan pengoptimalan performa pada sistem basis data modern.',
                'published_year' => 2019,
                'pages' => 1376,
                'language' => 'English',
                'is_featured' => false,
                'is_borrowable' => true,
                'publisher_name' => 'Pearson Education',
                'author_names' => ['Abraham Silberschatz'],
                'category_names' => ['Sistem Basis Data'],
            ],
            [
                'title' => 'Laskar Pelangi',
                'isbn' => '9789793062791',
                'description' => 'Novel fenomenal karya Andrea Hirata tentang perjuangan anak-anak di Belitung dalam menempuh pendidikan dasar yang menginspirasi banyak orang.',
                'published_year' => 2005,
                'pages' => 529,
                'language' => 'Indonesia',
                'is_featured' => false,
                'is_borrowable' => true,
                'publisher_name' => 'Elex Media Komputindo',
                'author_names' => ['Andrea Hirata'],
                'category_names' => ['Rekayasa Perangkat Lunak'],
            ],
        ];

        foreach ($booksData as $data) {
            $publisher = Publisher::query()->firstOrCreate(
                ['name' => $data['publisher_name']],
                ['slug' => Str::slug($data['publisher_name'])]
            );

            $book = Book::query()->firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']),
                    'description' => $data['description'],
                    'published_year' => $data['published_year'],
                    'pages' => $data['pages'],
                    'language' => $data['language'],
                    'is_featured' => $data['is_featured'],
                    'is_borrowable' => $data['is_borrowable'],
                    'is_published' => true,
                    'view_count' => rand(10, 100),
                    'publisher_id' => $publisher->id,
                ]
            );

            // Sync Authors
            $authorIds = [];
            foreach ($data['author_names'] as $authorName) {
                $author = Author::query()->firstOrCreate(
                    ['name' => $authorName],
                    ['slug' => Str::slug($authorName)]
                );
                $authorIds[] = $author->id;
            }
            $book->authors()->sync($authorIds);

            // Sync Categories
            $categoryIds = [];
            foreach ($data['category_names'] as $categoryName) {
                $category = Category::query()->firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    ['name' => $categoryName]
                );
                $categoryIds[] = $category->id;
            }
            $book->categories()->sync($categoryIds);

            // Create Book Items (copies) if they don't exist
            if ($book->items()->count() === 0) {
                $copyCount = rand(2, 4);
                for ($i = 1; $i <= $copyCount; $i++) {
                    BookItem::query()->create([
                        'book_id' => $book->id,
                        'internal_code' => strtoupper(substr($data['publisher_name'], 0, 2)).'-'.$book->id.'-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                        'status' => 'available',
                        'condition' => 'good',
                    ]);
                }
            }
        }
    }
}
