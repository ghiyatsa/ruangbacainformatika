<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('static_pages')) {
            return;
        }

        $timestamp = Carbon::now();

        $defaults = [
            [
                'page_key' => 'about',
                'title' => 'Tentang Layanan',
                'slug' => 'about',
                'summary' => $this->settingValue('about_summary', 'Informasi singkat tentang Ruang Baca Teknik Informatika Universitas Malikussaleh.'),
                'content' => $this->settingValue('about_content', <<<'HTML'
<h2>Ruang Baca yang Lebih Mudah Diakses</h2>
<p>Ruang Baca Teknik Informatika membantu mahasiswa dan dosen mencari buku, melihat informasi layanan, dan memakai fasilitas ruang baca dengan lebih mudah.</p>
<h3>Layanan utama</h3>
<ul>
    <li><strong>Katalog terpadu (OPAC).</strong> Pencarian online untuk buku, laporan kerja praktik, skripsi, tesis, dan dokumen lain yang tersedia di ruang baca.</li>
    <li><strong>Kiosk mandiri.</strong> Antarmuka kiosk di lokasi untuk pencatatan kunjungan, pendaftaran anggota, serta proses peminjaman dan pengembalian secara mandiri.</li>
    <li><strong>Sirkulasi dan pengajuan online.</strong> Pengguna terdaftar dapat mengajukan peminjaman buku, memantau batas pengembalian, dan meninjau riwayat layanan melalui akun masing-masing.</li>
    <li><strong>Pemeriksaan kemiripan dokumen.</strong> Fitur pemeriksaan awal untuk melihat kemiripan judul atau dokumen sebelum diajukan.</li>
</ul>
HTML),
            ],
            [
                'page_key' => 'privacy-policy',
                'title' => 'Kebijakan Privasi',
                'slug' => 'privacy-policy',
                'summary' => $this->settingValue('privacy_policy_summary', 'Ringkasan penggunaan dan perlindungan data pengguna di Ruang Baca Teknik Informatika.'),
                'content' => $this->settingValue('privacy_policy_content', <<<'HTML'
<h2>Prinsip pengelolaan data</h2>
<p>Data digunakan seperlunya untuk akun, peminjaman, dan kebutuhan layanan ruang baca.</p>
<h3>Data yang digunakan</h3>
<p>Data yang dapat digunakan meliputi identitas akun, email, nomor WhatsApp, riwayat peminjaman, serta aktivitas yang terkait dengan penggunaan layanan ruang baca.</p>
<h3>Tujuan penggunaan</h3>
<p>Data digunakan untuk autentikasi akun, proses peminjaman dan pengembalian, notifikasi layanan, pengelolaan buku, serta peningkatan mutu layanan.</p>
<h3>Penyimpanan dan perlindungan</h3>
<p>Kami berupaya menjaga keamanan data melalui pembatasan akses, pencatatan aktivitas layanan, dan penggunaan yang seperlunya.</p>
<h3>Hak pengguna</h3>
<p>Pengguna dapat meminta pembaruan data profil atau klarifikasi penggunaan data melalui kontak resmi yang disediakan program studi.</p>
<h3>Catatan</h3>
<p>Kebijakan ini dapat diperbarui menyesuaikan kebutuhan layanan dan ketentuan institusi.</p>
HTML),
            ],
            [
                'page_key' => 'terms-of-service',
                'title' => 'Syarat Layanan',
                'slug' => 'terms-of-service',
                'summary' => $this->settingValue('terms_of_service_summary', 'Ketentuan penggunaan layanan Ruang Baca Teknik Informatika.'),
                'content' => $this->settingValue('terms_of_service_content', <<<'HTML'
<h2>Ketentuan penggunaan layanan</h2>
<p>Layanan ini disediakan untuk mendukung kegiatan belajar dan penggunaan ruang baca.</p>
<ul>
    <li>Gunakan akun dan layanan sesuai kebutuhan yang sah.</li>
    <li>Jaga kerahasiaan akses akun dan hindari penggunaan oleh pihak lain.</li>
    <li>Hormati ketentuan peminjaman, pengembalian, dan penggunaan buku.</li>
    <li>Dilarang menyalahgunakan layanan, data, atau fasilitas pencarian.</li>
    <li>Konten dan layanan dapat disesuaikan mengikuti kebijakan program studi.</li>
</ul>
<h3>Catatan penting</h3>
<p>Ketentuan ini mengikuti kebijakan program studi dan dapat diperbarui sesuai kebutuhan layanan.</p>
HTML),
            ],
        ];

        foreach ($defaults as $page) {
            DB::table('static_pages')->updateOrInsert(
                ['page_key' => $page['page_key']],
                [
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'summary' => $page['summary'],
                    'content' => $page['content'],
                    'is_active' => true,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ],
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('static_pages')) {
            return;
        }

        DB::table('static_pages')
            ->whereIn('page_key', ['about', 'privacy-policy', 'terms-of-service'])
            ->delete();
    }

    protected function settingValue(string $key, string $default): string
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        $value = DB::table('settings')
            ->where('section', 'page_content')
            ->where('key', $key)
            ->value('value');

        return is_string($value) && trim($value) !== '' ? trim($value) : $default;
    }
};
