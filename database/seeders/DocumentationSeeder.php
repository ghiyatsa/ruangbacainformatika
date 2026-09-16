<?php

namespace Database\Seeders;

use App\Models\Documentation;
use App\Models\DocumentationCategory;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    /**
     * Isi panduan operasional awal untuk admin & staff.
     *
     * Semua nilai operasional mengikuti default di kode:
     * - Kuota pinjam      : 3 buku (library.loan_max_books)
     * - Durasi pinjam     : 7 hari (library.loan_duration_days)
     * - Jam kiosk         : 08:00–17:00 Asia/Jakarta (KioskIdlePolicy)
     * - Pacing WhatsApp   : 15 detik (services.fonnte.send_interval_seconds)
     * - Batas WA per hari : 10 pesan (services.fonnte.daily_limit)
     */
    public function run(): void
    {
        $categories = [
            'mulai' => [
                'name' => 'Memulai',
                'description' => 'Hal dasar sebelum mengelola sistem.',
                'sort_order' => 1,
            ],
            'manajemen-buku' => [
                'name' => 'Manajemen Buku',
                'description' => 'Menambah, mengubah, dan merawat data koleksi.',
                'sort_order' => 2,
            ],
            'layanan-anggota' => [
                'name' => 'Layanan Anggota',
                'description' => 'Keanggotaan, peminjaman, dan pengembalian.',
                'sort_order' => 3,
            ],
            'tugas-akhir' => [
                'name' => 'Tugas Akhir',
                'description' => 'Skripsi, laporan KP, dan pemeriksaan kemiripan.',
                'sort_order' => 4,
            ],
            'komunikasi' => [
                'name' => 'Komunikasi WhatsApp',
                'description' => 'Pengiriman OTP, pengingat, dan log pesan.',
                'sort_order' => 5,
            ],
            'kiosk' => [
                'name' => 'Kiosk Perpustakaan',
                'description' => 'Perangkat kiosk, PIN, dan API untuk aplikasi Flutter.',
                'sort_order' => 6,
            ],
            'operasional' => [
                'name' => 'Operasional & Perawatan',
                'description' => 'Tugas rutin, pemeliharaan, dan pemecahan masalah.',
                'sort_order' => 7,
            ],
        ];

        $categoryIds = [];

        foreach ($categories as $slug => $data) {
            $category = DocumentationCategory::query()->updateOrCreate(
                ['slug' => $slug],
                $data + ['slug' => $slug],
            );

            $categoryIds[$slug] = $category->getKey();
        }

        $pages = [
            [
                'category' => 'mulai',
                'title' => 'Peran Pengguna dan Hak Akses',
                'summary' => 'Siapa saja yang boleh masuk panel admin dan apa yang boleh mereka lakukan.',
                'sort_order' => 1,
                'content' => <<<'HTML'
<h2>Peran yang tersedia</h2>
<p>Sistem mengenal dua peran administratif di samping peran anggota biasa:</p>
<ul>
<li><strong>super_admin</strong> — akses penuh, termasuk pengaturan sistem dan peran pengguna.</li>
<li><strong>staff</strong> — akses operasional harian, tanpa pengaturan sensitif.</li>
<li><strong>member</strong> — anggota perpustakaan; bukan peran administratif dan tidak bisa masuk panel admin.</li>
</ul>
<h2>Siapa yang boleh masuk panel admin</h2>
<p>Panel admin hanya dapat dibuka oleh pengguna dengan peran <em>super_admin</em> atau <em>staff</em>. Anggota biasa yang mencoba membuka alamat admin akan ditolak.</p>
<h2>Catatan penting</h2>
<p>Panduan ini hanya dapat dibaca oleh pengguna yang sudah bisa masuk panel admin, sehingga aman untuk mencatat langkah-langkah internal.</p>
HTML,
            ],
            [
                'category' => 'manajemen-buku',
                'title' => 'Menambah dan Mengubah Data Buku',
                'summary' => 'Alur input buku baru, penulis, penerbit, dan kategori.',
                'sort_order' => 1,
                'content' => <<<'HTML'
<h2>Sebelum menambah buku</h2>
<p>Pastikan penulis dan penerbit sudah ada. Jika belum, tambahkan lebih dahulu agar tidak terjadi data ganda.</p>
<h2>Langkah menambah buku</h2>
<ol>
<li>Buka menu <strong>Manajemen Buku &rarr; Buku</strong>.</li>
<li>Tekan tombol tambah, lalu isi judul, penulis, penerbit, dan tahun terbit.</li>
<li>Isi ISBN atau ISSN. Sistem akan memeriksa duplikasi nomor identitas.</li>
<li>Simpan, lalu pastikan buku muncul di daftar.</li>
</ol>
<h2>Normalisasi metadata</h2>
<p>Ada perintah otomatis untuk merapikan kapitalisasi penulis/penerbit dan format ISSN:</p>
<pre><code>php artisan books:standardize-metadata</code></pre>
<p>Jalankan perintah ini setelah impor data dalam jumlah besar.</p>
HTML,
            ],
            [
                'category' => 'layanan-anggota',
                'title' => 'Alur Peminjaman dan Pengembalian Buku',
                'summary' => 'Kuota, durasi pinjam, dan langkah peminjaman lewat kiosk maupun admin.',
                'sort_order' => 1,
                'content' => <<<'HTML'
<h2>Ketentuan dasar (nilai bawaan)</h2>
<table>
<thead><tr><th>Ketentuan</th><th>Nilai bawaan</th></tr></thead>
<tbody>
<tr><td>Kuota pinjam per anggota</td><td>3 buku</td></tr>
<tr><td>Durasi peminjaman</td><td>7 hari</td></tr>
<tr><td>Jam layanan kiosk</td><td>08.00–17.00 WIB</td></tr>
</tbody>
</table>
<p>Nilai tersebut dapat diubah di <strong>Pengaturan &rarr; Perpustakaan</strong>.</p>
<h2>Syarat anggota boleh meminjam</h2>
<p>Anggota hanya dapat meminjam bila seluruh syarat berikut terpenuhi:</p>
<ol>
<li>Memakai email kampus resmi.</li>
<li>Akun sudah disetujui admin.</li>
<li>Nomor WhatsApp sudah diverifikasi.</li>
<li>Profil (nomor WhatsApp dan alamat) sudah lengkap.</li>
<li>Tidak sedang dalam pembekuan hak pinjam.</li>
</ol>
<h2>Pembekuan hak pinjam karena keterlambatan</h2>
<p>Fitur ini <strong>nonaktif secara bawaan</strong>. Bila diaktifkan di pengaturan, anggota yang mengembalikan buku melewati ambang batas keterlambatan akan dibekukan sementara.</p>
HTML,
            ],
            [
                'category' => 'layanan-anggota',
                'title' => 'Menyetujui Akun Anggota Baru',
                'summary' => 'Kapan akun butuh persetujuan manual dan cara menyetujuinya.',
                'sort_order' => 2,
                'content' => <<<'HTML'
<h2>Siapa yang disetujui otomatis</h2>
<p>Mahasiswa Teknik Informatika dikenali dari email kampus dengan NIM berpola tertentu dan akan <strong>disetujui otomatis</strong>. Anggota kampus lain perlu persetujuan manual.</p>
<h2>Cara menyetujui</h2>
<ol>
<li>Buka <strong>Manajemen Pengguna &rarr; Pengguna</strong>.</li>
<li>Gunakan penyaring <em>Perlu disetujui admin</em> untuk melihat daftar tunggu.</li>
<li>Buka akun yang dituju, aktifkan <strong>Persetujuan Akun</strong>, lalu simpan.</li>
</ol>
<h2>Hal yang perlu dipahami</h2>
<p>Persetujuan akun hanyalah <em>satu</em> dari beberapa syarat peminjaman. Mengaktifkannya untuk pengguna non-kampus <strong>tidak</strong> membuat mereka bisa meminjam, karena syarat email kampus tetap berlaku.</p>
HTML,
            ],
            [
                'category' => 'tugas-akhir',
                'title' => 'Mengelola Skripsi dan Laporan KP',
                'summary' => 'Perbedaan kedua jenis dokumen dan cara publikasinya.',
                'sort_order' => 1,
                'content' => <<<'HTML'
<h2>Dua jenis dokumen</h2>
<ul>
<li><strong>Skripsi</strong> — tugas akhir mahasiswa program sarjana.</li>
<li><strong>Laporan KP</strong> — laporan kerja praktek/magang.</li>
</ul>
<p>Keduanya dikelola di menu masing-masing dan memiliki alur publikasi serupa.</p>
<h2>Status publikasi</h2>
<p>Dokumen yang baru diajukan berstatus menunggu peninjauan. Setelah disetujui, dokumen akan tampil di katalog publik. Jika ditolak, pengaju akan menerima notifikasi berisi alasan penolakan.</p>
HTML,
            ],
            [
                'category' => 'tugas-akhir',
                'title' => 'Pemeriksaan Kemiripan Dokumen',
                'summary' => 'Cara kerja skor kemiripan dan kapan perlu sinkronisasi ulang.',
                'sort_order' => 2,
                'content' => <<<'HTML'
<h2>Bagaimana skor dihitung</h2>
<p>Skor kemiripan dihitung dari perbandingan judul, abstrak, dan kata kunci. Bobot tiap bagian dapat diatur di halaman pengaturan integrasi.</p>
<h2>Sinkronisasi data</h2>
<p>Untuk mengirim seluruh dokumen ke layanan kemiripan, gunakan perintah berikut:</p>
<pre><code>php artisan skripsi:sync</code></pre>
<p>Perintah ini berjalan langsung (tanpa antrean), sehingga cocok untuk server yang tidak memiliki pemroses antrean.</p>
<h2>Reset cache hasil</h2>
<p>Jika skor tampak tidak sesuai setelah perubahan data, bersihkan cache hasil pemeriksaan:</p>
<pre><code>php artisan similarity:clear-cache</code></pre>
HTML,
            ],
            [
                'category' => 'komunikasi',
                'title' => 'Verifikasi WhatsApp dan Pengiriman OTP',
                'summary' => 'Siapa yang menerima OTP, batasnya, dan cara mengatasi kegagalan kirim.',
                'sort_order' => 1,
                'content' => <<<'HTML'
<h2>Siapa yang menerima OTP</h2>
<p>Hanya pengguna dengan <strong>email kampus</strong> yang perlu memverifikasi WhatsApp, karena hanya mereka yang dapat meminjam buku. Pengguna non-kampus maupun akun admin tidak diminta verifikasi.</p>
<h2>Pengaman pengiriman</h2>
<table>
<thead><tr><th>Pengaman</th><th>Nilai bawaan</th></tr></thead>
<tbody>
<tr><td>Jeda antar pesan</td><td>15 detik</td></tr>
<tr><td>Jeda khusus OTP</td><td>5 detik</td></tr>
<tr><td>Batas pesan per nomor per hari</td><td>10 pesan</td></tr>
<tr><td>Jeda otomatis saat banyak gagal</td><td>5 kegagalan dalam 15 menit</td></tr>
</tbody>
</table>
<h2>Bila kode tidak sampai</h2>
<ol>
<li>Pastikan nomor WhatsApp aktif dan benar.</li>
<li>Tunggu jeda berikutnya; pengiriman berikutnya akan dibatasi sementara.</li>
<li>Periksa menu <strong>Log WhatsApp</strong> untuk melihat status pengiriman terakhir.</li>
</ol>
HTML,
            ],
            [
                'category' => 'komunikasi',
                'title' => 'Pengingat Pengembalian Buku Otomatis',
                'summary' => 'Kapan pengingat dikirim dan cara menjalankannya secara manual.',
                'sort_order' => 2,
                'content' => <<<'HTML'
<h2>Kapan pengingat dikirim</h2>
<p>Pengingat dikirim otomatis setiap hari pada pukul <strong>08.00 WIB</strong> kepada anggota dengan pinjaman yang mendekati atau melewati batas pengembalian.</p>
<h2>Menjalankan manual</h2>
<pre><code>php artisan app:remind-return</code></pre>
<h2>Mencegah pengiriman ganda</h2>
<p>Anggota yang sudah menerima pengingat pada hari yang sama tidak akan menerima pengingat kedua, sehingga tidak ada pesan berulang.</p>
<h2>Catatan zona waktu</h2>
<p>Penjadwalan memakai zona waktu <em>Asia/Jakarta</em>. Jika jadwal tampak bergeser beberapa jam saat diperiksa dengan perintah penjadwalan, perlu diingat bahwa tampilan tersebut menggunakan UTC — bukan waktu layanan sebenarnya.</p>
HTML,
            ],
            [
                'category' => 'kiosk',
                'title' => 'PIN dan Jam Operasional Kiosk',
                'summary' => 'Mengatur PIN kiosk dan batas jam layanan perangkat.',
                'sort_order' => 1,
                'content' => <<<'HTML'
<h2>Jam operasional</h2>
<p>Secara bawaan kiosk hanya melayani dari <strong>08.00 sampai 17.00 WIB</strong>. Di luar jam tersebut kiosk menolak permintaan. Jam ini dapat diubah di pengaturan perpustakaan.</p>
<h2>PIN kiosk</h2>
<p>PIN dipakai sekali saat aktivasi perangkat. Setelah aktif, perangkat memakai kunci API, bukan PIN.</p>
<h2>Bila kiosk menolak beroperasi</h2>
<ol>
<li>Periksa jam perangkat — pastikan berada dalam jam layanan.</li>
<li>Pastikan perangkat tersambung ke jaringan perpustakaan.</li>
<li>Jika perangkat sudah dihapus, lakukan aktivasi ulang dengan PIN.</li>
</ol>
HTML,
            ],
            [
                'category' => 'kiosk',
                'title' => 'Mengelola Kunci API Aplikasi Kiosk',
                'summary' => 'Membuat, melihat, dan mencabut kunci API untuk aplikasi Flutter.',
                'sort_order' => 2,
                'content' => <<<'HTML'
<h2>Kegunaan</h2>
<p>Aplikasi kiosk berbasis Flutter berkomunikasi dengan server memakai satu kunci API bersama. PIN tidak lagi dipakai setiap hari.</p>
<h2>Perintah yang tersedia</h2>
<pre><code>php artisan kiosk:api-key list
php artisan kiosk:api-key generate --force
php artisan kiosk:api-key revoke</code></pre>
<h2>Bila kunci diduga bocor</h2>
<ol>
<li>Cabut kunci lama dengan <code>revoke</code>.</li>
<li>Buat kunci baru dengan <code>generate</code>.</li>
<li>Perbarui konfigurasi di seluruh perangkat kiosk.</li>
</ol>
<p>Pencabutan berlaku seketika, sehingga perangkat yang memakai kunci lama langsung ditolak.</p>
HTML,
            ],
            [
                'category' => 'operasional',
                'title' => 'Tugas Harian Otomatis',
                'summary' => 'Pembersihan rutin yang berjalan sendiri dan perintah jalan manualnya.',
                'sort_order' => 1,
                'content' => <<<'HTML'
<h2>Jadwal otomatis</h2>
<table>
<thead><tr><th>Waktu (WIB)</th><th>Tugas</th></tr></thead>
<tbody>
<tr><td>08.00</td><td>Kirim pengingat pengembalian buku</td></tr>
<tr><td>03.00</td><td>Pembersihan harian</td></tr>
</tbody>
</table>
<h2>Isi pembersihan harian</h2>
<ul>
<li>Menghapus catatan sementara yang sudah tidak aktif (termasuk sesi perangkat kiosk).</li>
<li>Menghapus notifikasi internal yang lama.</li>
<li>Menghapus log pengiriman WhatsApp yang lama.</li>
<li>Menghapus riwayat pencarian yang lama.</li>
<li>Menghapus log aktivitas dan kunjungan yang lama.</li>
<li>Membersihkan pekerjaan antrean yang gagal.</li>
</ul>
<h2>Menjalankan manual</h2>
<pre><code>php artisan app:prune-temporary-records
php artisan app:prune-notifications
php artisan app:prune-whatsapp-logs
php artisan app:prune-search-history
php artisan app:prune-audit-logs</code></pre>
HTML,
            ],
            [
                'category' => 'operasional',
                'title' => 'Pemecahan Masalah Umum',
                'summary' => 'Langkah cepat untuk masalah yang paling sering terjadi.',
                'sort_order' => 2,
                'content' => <<<'HTML'
<h2>Perubahan kode tidak terlihat</h2>
<p>Setelah pemasangan versi baru, muat ulang layanan PHP agar perubahan terbaca. Gejala yang muncul bila langkah ini terlewat adalah halaman baru berbalas <em>404</em> atau permintaan yang seharusnya sah malah ditolak.</p>
<h2>Pengingat tidak terkirim</h2>
<ol>
<li>Pastikan ada pinjaman yang benar-benar jatuh tempo.</li>
<li>Jalankan <code>php artisan app:remind-return</code> secara manual untuk melihat keterangan.</li>
<li>Periksa <strong>Log WhatsApp</strong>.</li>
</ol>
<h2>Notifikasi WhatsApp tidak sampai</h2>
<ol>
<li>Periksa status perangkat di halaman pengaturan integrasi — pastikan tertulis tersambung.</li>
<li>Periksa batas harian; satu nomor dibatasi 10 pesan per hari.</li>
<li>Hindari mengirim banyak pesan dalam waktu singkat.</li>
</ol>
<h2>Anggota tidak bisa meminjam</h2>
<p>Buka akun anggota dan periksa satu per satu syaratnya: email kampus, persetujuan akun, verifikasi WhatsApp, kelengkapan profil, dan ada tidaknya pembekuan.</p>
HTML,
            ],
        ];

        foreach ($pages as $page) {
            Documentation::query()->updateOrCreate(
                ['slug' => str($page['title'])->slug()->value()],
                [
                    'documentation_category_id' => $categoryIds[$page['category']],
                    'title' => $page['title'],
                    'slug' => str($page['title'])->slug()->value(),
                    'summary' => $page['summary'],
                    'content' => $page['content'],
                    'is_published' => true,
                    'sort_order' => $page['sort_order'],
                ],
            );
        }
    }
}
