<?php

namespace Database\Seeders;

use App\Models\InternshipReport;
use Illuminate\Database\Seeder;

class InternshipReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reports = [
            [
                'title' => 'Laporan Kerja Praktik: Pengembangan Aplikasi Sistem Layanan Pengaduan Masyarakat pada Dinas Komunikasi dan Informatika Aceh Utara',
                'author_name' => 'Gita Lestari',
                'student_id' => '210170014',
                'year' => 2024,
                'abstract' => 'Laporan ini mendokumentasikan pelaksanaan Kerja Praktik selama 2 bulan di Diskominfo Aceh Utara. Fokus kegiatan adalah merancang modul pengaduan masyarakat berbasis web untuk meningkatkan transparansi pelayanan publik.',
                'keywords' => 'kerja praktik, diskominfo, pengaduan masyarakat, laravel',
            ],
            [
                'title' => 'Laporan Kerja Praktik: Administrasi Jaringan dan Pemeliharaan Server di UPT Teknologi Informasi dan Komunikasi Universitas Malikussaleh',
                'author_name' => 'Hendra Wijaya',
                'student_id' => '210170098',
                'year' => 2024,
                'abstract' => 'Kerja Praktik dilaksanakan di UPT TIK Universitas Malikussaleh dengan tugas melakukan pemantauan jaringan, konfigurasi mikrotik, dan pemeliharaan rutin server penunjang aplikasi akademik.',
                'keywords' => 'kerja praktik, upt tik, mikrotik, administrasi jaringan, server',
            ],
        ];

        foreach ($reports as $data) {
            InternshipReport::query()->firstOrCreate(
                ['student_id' => $data['student_id']],
                $data
            );
        }
    }
}
