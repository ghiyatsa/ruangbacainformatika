<?php

namespace Database\Seeders;

use App\Models\Skripsi;
use Illuminate\Database\Seeder;

class SkripsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skripsis = [
            [
                'title' => 'Rancang Bangun Sistem Informasi Ruang Baca Teknik Informatika Universitas Malikussaleh Berbasis Web',
                'author_name' => 'Ahmad Fauzi',
                'student_id' => '200170042',
                'year' => 2024,
                'abstract' => 'Penelitian ini bertujuan untuk merancang dan membangun sistem informasi ruang baca digital di Jurusan Teknik Informatika Universitas Malikussaleh. Sistem ini mempermudah proses pencarian katalog, pelaporan buku bermasalah, dan pengelolaan peminjaman secara mandiri melalui perangkat kiosk.',
                'keywords' => 'sistem informasi, ruang baca, laravel, teknik informatika, unimal',
            ],
            [
                'title' => 'Implementasi Algoritma Naive Bayes Classifier untuk Klasifikasi Skripsi Mahasiswa Teknik Informatika',
                'author_name' => 'Budi Santoso',
                'student_id' => '200170123',
                'year' => 2024,
                'abstract' => 'Klasifikasi skripsi ke dalam bidang keahlian (rekayasa perangkat lunak, sistem informasi, jaringan, dll.) secara manual membutuhkan waktu lama. Penelitian ini menggunakan algoritma Naive Bayes Classifier untuk mengotomatisasi pengelompokan berdasarkan kata kunci dan abstrak skripsi.',
                'keywords' => 'naive bayes, klasifikasi, machine learning, text mining',
            ],
            [
                'title' => 'Penerapan Arsitektur Microservices pada Sistem Manajemen Peminjaman Buku Perpustakaan',
                'author_name' => 'Citra Dewi',
                'student_id' => '200170089',
                'year' => 2023,
                'abstract' => 'Sistem perpustakaan monolitik sering kali mengalami kendala skalabilitas. Skripsi ini mengusulkan desain arsitektur microservices menggunakan Docker dan Laravel untuk memisahkan modul katalog, peminjaman, dan notifikasi agar lebih andal dan mudah dikembangkan.',
                'keywords' => 'microservices, docker, laravel, perpustakaan, arsitektur',
            ],
            [
                'title' => 'Deteksi Kemiripan Judul Karya Ilmiah Menggunakan Model IndoBERT dan Cosine Similarity',
                'author_name' => 'Dani Kurniawan',
                'student_id' => '190170155',
                'year' => 2023,
                'abstract' => 'Penelitian ini membangun sistem deteksi plagiarisme dan kemiripan proposal skripsi mahasiswa Teknik Informatika. Dengan memanfaatkan pemrosesan bahasa alami (NLP) berbasis model IndoBERT dan perhitungan Cosine Similarity, sistem dapat mengidentifikasi kesamaan ide dengan lebih akurat dibanding pencarian teks biasa.',
                'keywords' => 'indobert, cosine similarity, deteksi kemiripan, nlp, skripsi',
            ],
            [
                'title' => 'Pengembangan Aplikasi E-Voting Pemilihan Ketua Himpunan Mahasiswa Informatika Berbasis Blockchain',
                'author_name' => 'Eko Prasetyo',
                'student_id' => '190170012',
                'year' => 2023,
                'abstract' => 'Pemilihan ketua himpunan mahasiswa sering kali menghadapi isu transparansi dan keamanan data. Aplikasi e-voting berbasis blockchain Ethereum ini dirancang untuk memastikan setiap suara tercatat dengan aman, tidak dapat dimanipulasi, dan dapat diverifikasi secara transparan.',
                'keywords' => 'e-voting, blockchain, ethereum, himpunan mahasiswa, keamanan',
            ],
        ];

        foreach ($skripsis as $data) {
            Skripsi::query()->firstOrCreate(
                ['student_id' => $data['student_id']],
                $data
            );
        }
    }
}
