<?php

namespace Database\Seeders;

use App\Models\Thesis;
use Illuminate\Database\Seeder;

class ThesisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $theses = [
            [
                'title' => 'Analisis Performa Deep Learning ResNet-50 pada Klasifikasi Penyakit Daun Tanaman Kelapa Sawit',
                'author_name' => 'Dr. Riza Wahyu',
                'student_id' => '220170501',
                'year' => 2024,
                'abstract' => 'Penyakit daun kelapa sawit merupakan salah satu penyebab utama penurunan hasil panen. Penelitian tesis ini mengusulkan model klasifikasi otomatis citra daun berbasis Convolutional Neural Network (CNN) dengan arsitektur ResNet-50 untuk mendeteksi penyakit secara dini dengan akurasi tinggi.',
                'keywords' => 'deep learning, resnet-50, kelapa sawit, citra daun, klasifikasi',
            ],
            [
                'title' => 'Sistem Deteksi Intrusi Jaringan Nirkabel Berbasis Kombinasi Random Forest dan Principal Component Analysis',
                'author_name' => 'Faisal Rahman',
                'student_id' => '220170509',
                'year' => 2023,
                'abstract' => 'Keamanan jaringan nirkabel rentan terhadap serangan brute-force dan spoofing. Tesis ini mengembangkan model Network Intrusion Detection System (NIDS) yang menggabungkan Principal Component Analysis (PCA) untuk reduksi dimensi fitur dan Random Forest untuk klasifikasi serangan siber.',
                'keywords' => 'intrusion detection, random forest, pca, wireless security',
            ],
        ];

        foreach ($theses as $data) {
            Thesis::query()->firstOrCreate(
                ['student_id' => $data['student_id']],
                $data
            );
        }
    }
}
