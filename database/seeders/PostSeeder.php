<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deleting existing post records to allow clean re-seeding
        DB::table('post_category_post')->delete();
        DB::table('post_post_tag')->delete();
        Post::query()->delete();
        PostCategory::query()->delete();
        PostTag::query()->delete();

        $users = User::all();

        if ($users->isEmpty()) {
            // Fallback user if database is empty
            $users = collect([
                User::factory()->create([
                    'name' => 'Admin Ruang Baca',
                    'email' => 'admin@unimal.ac.id',
                    'is_approved' => true,
                ]),
            ]);
        }

        // 1. Seed Categories
        $categoriesData = [
            ['name' => 'Tips Akademik', 'description' => 'Panduan, tips, dan trik seputar perkuliahan dan tugas akhir.'],
            ['name' => 'Event & Kegiatan', 'description' => 'Informasi acara, webinar, dan kegiatan di lingkungan Ruang Baca.'],
            ['name' => 'Teknologi & Coding', 'description' => 'Tutorial pemrograman, riset teknologi, dan tren software engineering.'],
            ['name' => 'Resensi Buku', 'description' => 'Ulasan buku-buku menarik yang tersedia di Ruang Baca.'],
            ['name' => 'Pengumuman', 'description' => 'Informasi penting dan resmi dari pengelola Ruang Baca.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = PostCategory::create($cat);
        }

        // 2. Seed Tags
        $tagsData = [
            ['name' => 'Laravel', 'description' => 'Materi seputar Laravel Framework.'],
            ['name' => 'React', 'description' => 'Materi seputar library UI React.'],
            ['name' => 'Tips Skripsi', 'description' => 'Tips dan trik mengerjakan tugas akhir.'],
            ['name' => 'Informatika', 'description' => 'Topik umum Teknik Informatika.'],
            ['name' => 'Perpustakaan', 'description' => 'Topik seputar Ruang Baca dan literasi.'],
            ['name' => 'AI', 'description' => 'Materi kecerdasan buatan.'],
            ['name' => 'Magang', 'description' => 'Informasi dan tips program magang.'],
            ['name' => 'Git', 'description' => 'Version control menggunakan Git dan GitHub.'],
        ];

        $tags = [];
        foreach ($tagsData as $t) {
            $tags[$t['name']] = PostTag::create($t);
        }

        // 3. Seed Indonesian Articles
        $postsData = [
            [
                'title' => 'Tips Menembus Jurnal Internasional Terindeks Scopus untuk Mahasiswa Informatika',
                'summary' => 'Bagaimana cara menulis paper ilmiah berkualitas yang layak dipublikasikan di jurnal internasional? Simak panduan lengkap langkah-demi-langkah bagi mahasiswa.',
                'content' => '<p>Menulis artikel ilmiah yang berhasil menembus jurnal terindeks Scopus merupakan pencapaian luar biasa bagi mahasiswa Teknik Informatika. Selain meningkatkan reputasi akademis, publikasi ilmiah juga menjadi poin plus untuk kelulusan dan karir masa depan.</p>
<p>Langkah pertama dalam menulis paper berkualitas adalah memilih topik riset yang orisinal dan memiliki kontribusi nyata (novelty). Cari celah penelitian dari paper-paper terbaru yang bisa Anda temukan di repositori atau koleksi fisik Ruang Baca Informatika.</p>
<p>Langkah kedua adalah menyusun struktur paper dengan baik, yang umumnya terdiri dari:
<ul>
  <li><strong>Abstract:</strong> Ringkasan singkat mengenai masalah, metode, hasil, dan kesimpulan.</li>
  <li><strong>Introduction:</strong> Latar belakang masalah, penelitian terdahulu, dan novelty penelitian Anda.</li>
  <li><strong>Methodology:</strong> Penjelasan detail alur sistem, algoritma, atau metode eksperimen yang digunakan.</li>
  <li><strong>Results and Discussion:</strong> Penyajian data hasil pengujian beserta analisis mendalam.</li>
  <li><strong>Conclusion:</strong> Kesimpulan akhir dan saran untuk penelitian selanjutnya.</li>
</ul>
</p>
<p>Pastikan Anda menggunakan referensi dari jurnal terpercaya dan menghindari jurnal predator. Untuk referensi buku cetak penunjang metode penelitian, Anda bisa langsung meminjamnya di Ruang Baca Informatika menggunakan sistem layanan mandiri QR Code.</p>',
                'status' => Post::STATUS_APPROVED,
                'categories' => ['Tips Akademik', 'Teknologi & Coding'],
                'tags' => ['Tips Skripsi', 'Informatika', 'Perpustakaan'],
                'view_count' => rand(120, 450),
            ],
            [
                'title' => 'Mengenal Git dan GitHub: Senjata Wajib Developer Modern',
                'summary' => 'Ingin berkolaborasi dalam proyek software dengan rapi? Git adalah jawabannya. Pelajari perintah dasar Git dan tips mengelola repositori GitHub.',
                'content' => '<p>Dalam dunia software development, kolaborasi adalah kunci. Ketika bekerja dalam tim, mengedit file kode yang sama secara bersamaan sering kali memicu konflik file. Di sinilah Version Control System seperti Git menjadi penyelamat.</p>
<p>Git memungkinkan Anda mencatat setiap perubahan kode, kembali ke versi sebelumnya jika terjadi error, serta membuat cabang (branch) untuk eksperimen fitur baru tanpa merusak kode utama (main/master).</p>
<p>Berikut adalah beberapa perintah dasar Git yang wajib dihafal:
<pre><code># Inisialisasi repositori lokal
git init

# Menambahkan perubahan ke staging area
git add .

# Menyimpan perubahan ke riwayat
git commit -m "feat: add login page"

# Menghubungkan ke repositori remote (GitHub)
git remote add origin &lt;url-repositori&gt;

# Mengirimkan perubahan ke GitHub
git push origin main</code></pre>
</p>
<p>Dengan menguasai Git dan GitHub, Anda tidak hanya mempermudah pengerjaan tugas kelompok atau skripsi, tetapi juga membangun portofolio yang sangat dihargai oleh industri teknologi.</p>',
                'status' => Post::STATUS_APPROVED,
                'categories' => ['Teknologi & Coding'],
                'tags' => ['Git', 'Informatika'],
                'view_count' => rand(80, 300),
            ],
            [
                'title' => 'Panduan Memilih Topik Skripsi Informatika yang Menarik dan Cepat Selesai',
                'summary' => 'Masih bingung mencari ide tugas akhir? Jangan khawatir. Kami rangkum beberapa topik tren seperti AI, IoT, dan Web Development yang bisa kamu angkat.',
                'content' => '<p>Menentukan topik skripsi sering kali menjadi fase paling berat bagi mahasiswa tingkat akhir. Rasa takut salah memilih topik atau kesulitan mencari data sering kali membuat mahasiswa menunda-nunda memulai tugas akhir.</p>
<p>Untuk membantu Anda, berikut adalah beberapa area penelitian populer dan relevan di bidang Teknik Informatika saat ini:
<ol>
  <li><strong>Artificial Intelligence & Machine Learning:</strong> Deteksi objek menggunakan Computer Vision, analisis sentimen media sosial, atau sistem rekomendasi.</li>
  <li><strong>Internet of Things (IoT):</strong> Sistem monitoring pertanian pintar (smart farming), otomasi rumah pintar, atau monitoring kesehatan berbasis sensor.</li>
  <li><strong>Web & Mobile Application:</strong> Membangun aplikasi dengan teknologi mutakhir (seperti Laravel, React, Next.js) dengan penyelesaian masalah spesifik di masyarakat.</li>
</ol>
</p>
<p>Tips penting agar skripsi cepat selesai: pilihlah topik yang Anda kuasai, konsultasikan secara intensif dengan dosen pembimbing sejak awal, dan manfaatkan repositori skripsi di Ruang Baca Informatika untuk melihat struktur penelitian terdahulu yang sukses.</p>',
                'status' => Post::STATUS_APPROVED,
                'categories' => ['Tips Akademik'],
                'tags' => ['Tips Skripsi', 'Informatika'],
                'view_count' => rand(150, 600),
            ],
            [
                'title' => 'Pentingnya Ikut Magang Sebelum Lulus: Pengalaman Nyata Mahasiswa Informatika',
                'summary' => 'Magang bukan sekadar prasyarat kelulusan, tapi gerbang karir sesungguhnya. Baca kisah sukses alumni dalam menjalani magang industri.',
                'content' => '<p>Dunia perkuliahan sering kali menyajikan materi yang bersifat teoritis. Namun, dunia industri menuntut keterampilan praktis dan kesiapan kerja. Kesenjangan ini bisa dijembatani secara efektif melalui program magang (internship).</p>
<p>Melalui magang, Anda akan belajar bagaimana bekerja dalam tim profesional, berkomunikasi dengan klien, menggunakan tools kolaborasi industri, dan merasakan langsung tekanan serta budaya kerja nyata.</p>
<p>Beberapa keuntungan magang bagi mahasiswa Informatika:
<ul>
  <li>Mendapatkan portofolio proyek riil yang bisa dicantumkan di CV.</li>
  <li>Membangun jaringan (networking) dengan para profesional industri.</li>
  <li>Berkesempatan ditawari kerja langsung sebagai karyawan tetap setelah lulus.</li>
</ul>
</p>
<p>Jangan lupa untuk mempersiapkan laporan magang Anda dengan baik setelah selesai. Contoh-contoh laporan magang terdahulu bisa Anda akses di perpustakaan Ruang Baca Informatika sebagai acuan penulisan.</p>',
                'status' => Post::STATUS_APPROVED,
                'categories' => ['Tips Akademik', 'Event & Kegiatan'],
                'tags' => ['Magang', 'Informatika'],
                'view_count' => rand(90, 250),
            ],
            [
                'title' => 'Ruang Baca Informatika Malikussaleh Kini Hadir dengan Sistem Layanan Mandiri Kiosk!',
                'summary' => 'Kabar gembira! Ruang Baca kini dilengkapi dengan sistem peminjaman mandiri berbasis QR code untuk memudahkan sirkulasi buku.',
                'content' => '<p>Untuk meningkatkan kenyamanan dan efisiensi sirkulasi buku, Ruang Baca Teknik Informatika kini resmi meluncurkan platform sistem informasi perpustakaan baru.</p>
<p>Sistem ini mengusung konsep layanan mandiri (kiosk), di mana mahasiswa dapat mencari buku di katalog digital, memasukkan buku ke draf peminjaman, lalu melakukan scan QR code draf di layar kiosk yang tersedia di ruang baca. Petugas atau admin cukup melakukan verifikasi draf tersebut untuk meresmikan peminjaman.</p>
<p>Langkah-langkah menggunakan layanan baru ini:
<ol>
  <li>Login ke web menggunakan akun email Universitas Malikussaleh (Google Auth).</li>
  <li>Lengkapi profil dan verifikasi nomor WhatsApp Anda untuk mengaktifkan hak peminjaman.</li>
  <li>Cari buku yang ingin dipinjam, masukkan ke draf peminjaman.</li>
  <li>Kunjungi Ruang Baca, scan draf QR di Kiosk atau tunjukkan ke petugas.</li>
</ol>
</p>
<p>Selamat mencoba sistem baru ini! Semoga minat baca mahasiswa Informatika terus meningkat.</p>',
                'status' => Post::STATUS_APPROVED,
                'categories' => ['Pengumuman', 'Event & Kegiatan'],
                'tags' => ['Perpustakaan', 'Informatika'],
                'view_count' => rand(200, 700),
            ],
            [
                'title' => 'Membangun REST API Cepat dengan Laravel 11 dan Sanctum',
                'summary' => 'Laravel tetap menjadi salah satu framework PHP terpopuler di dunia. Pelajari cara membangun REST API yang aman dalam hitungan menit.',
                'content' => '<p>REST API merupakan standar industri dalam menghubungkan aplikasi backend dengan frontend (seperti React, Vue, atau Mobile Apps). Laravel menyederhanakan pembuatan API ini dengan fitur-fitur bawaan yang sangat kuat.</p>
<p>Dalam tutorial singkat ini, kita akan membuat API sederhana menggunakan Laravel 11 dan Laravel Sanctum untuk sistem autentikasi token.</p>
<p>Langkah instalasi Laravel Sanctum:
<pre><code>composer require laravel/sanctum
php artisan sanctum:install</code></pre>
</p>
<p>Selanjutnya, Anda dapat membuat controller menggunakan API resource untuk menangani operasi CRUD secara terstruktur dan efisien. Jangan lupa untuk membatasi akses endpoint sensitif menggunakan middleware auth:sanctum.</p>',
                'status' => Post::STATUS_APPROVED,
                'categories' => ['Teknologi & Coding'],
                'tags' => ['Laravel', 'Informatika'],
                'view_count' => rand(50, 180),
            ],
            [
                'title' => '5 Rekomendasi Buku Rekayasa Perangkat Lunak Terbaik di Ruang Baca',
                'summary' => 'Ingin memperdalam arsitektur software dan design patterns? Berikut adalah 5 buku best-seller di Ruang Baca yang wajib kamu pinjam.',
                'content' => '<p>Buku cetak tetap memiliki nilai mendalam bagi mahasiswa yang ingin memahami konsep software engineering secara komprehensif. Koleksi buku di Ruang Baca Informatika dirancang untuk menunjang kurikulum utama perkuliahan.</p>
<p>Berikut adalah 5 rekomendasi buku rekayasa perangkat lunak (RPL) terbaik di Ruang Baca yang wajib Anda baca:
<ol>
  <li><strong>Clean Code</strong> oleh Robert C. Martin - Memandu Anda menulis kode yang elegan dan mudah dipelihara.</li>
  <li><strong>Refactoring</strong> oleh Martin Fowler - Teknik meningkatkan struktur kode tanpa mengubah perilakunya.</li>
  <li><strong>Design Patterns</strong> oleh GoF - Pola-pola arsitektur standar untuk mengatasi masalah pemrograman berulang.</li>
  <li><strong>Laravel Up & Running</strong> oleh Matt Stauffer - Buku wajib bagi yang ingin menguasai ekosistem Laravel secara mendalam.</li>
  <li><strong>Database System Concepts</strong> - Membedah teori basis data relasional hingga optimasi query.</li>
</ol>
</p>
<p>Seluruh buku di atas sudah terdaftar di katalog online kami dan siap dipinjam kapan saja.</p>',
                'status' => Post::STATUS_APPROVED,
                'categories' => ['Resensi Buku'],
                'tags' => ['Perpustakaan', 'Informatika'],
                'view_count' => rand(60, 220),
            ],
            [
                'title' => 'Pemanfaatan Artificial Intelligence (AI) dalam Penelitian Mahasiswa',
                'summary' => 'Bagaimana mahasiswa dapat memanfaatkan tool AI secara bijak untuk membantu riset akademis tanpa melanggar kode etik plagiarisme?',
                'content' => '<p>Kehadiran alat bantu AI seperti ChatGPT dan GitHub Copilot telah mengubah cara kerja developer dan akademisi. Namun, di dunia akademis, penggunaannya harus disikapi dengan bijak untuk menjaga integritas keilmuan.</p>
<p>AI dapat digunakan secara positif untuk curah pendapat ide, memperbaiki tata bahasa Inggris pada draf paper, atau membantu memahami baris kode yang kompleks.</p>
<p>Namun, dilarang keras menyalin mentah-mentah hasil tulisan dari AI ke dalam naskah skripsi karena melanggar kode etik akademis. Jadikan AI sebagai asisten belajar, bukan penulis pengganti Anda.</p>',
                'status' => Post::STATUS_APPROVED,
                'categories' => ['Tips Akademik', 'Teknologi & Coding'],
                'tags' => ['AI', 'Informatika', 'Tips Skripsi'],
                'view_count' => rand(130, 400),
            ],
            [
                'title' => 'Draft Artikel: Belajar Dasar React 19 dan Concurrent Features',
                'summary' => 'React 19 membawa banyak pembaruan menarik bagi ekosistem frontend web. Yuk intip beberapa fitur baru yang akan mempermudah developer.',
                'content' => '<p>Ini adalah draf artikel tentang React 19. React 19 menghadirkan compiler baru bernama React Compiler (sebelumnya React Forget) yang secara otomatis melakukan memoisasi komponen tanpa perlu useMemo atau useCallback secara manual.</p>',
                'status' => Post::STATUS_DRAFT,
                'categories' => ['Teknologi & Coding'],
                'tags' => ['React'],
                'view_count' => 0,
            ],
            [
                'title' => 'Draft Pengumuman: Jadwal Maintenance Sistem Ruang Baca Juli 2026',
                'summary' => 'Pengumuman mengenai rencana pemeliharaan server database guna meningkatkan performa sinkronisasi kemiripan skripsi.',
                'content' => '<p>Ini adalah draf pengumuman pemeliharaan sistem. Server database akan dinonaktifkan sementara pada hari Sabtu, 4 Juli 2026 pukul 22:00 WIB hingga Minggu, 5 Juli 2026 pukul 04:00 WIB.</p>',
                'status' => Post::STATUS_DRAFT,
                'categories' => ['Pengumuman'],
                'tags' => ['Perpustakaan'],
                'view_count' => 0,
            ],
            [
                'title' => 'Review Pending: Menulis Clean Code dengan Konsep SOLID',
                'summary' => 'Prinsip SOLID merupakan lima pedoman desain berorientasi objek yang membantu membuat perangkat lunak lebih fleksibel dan mudah dipelihara.',
                'content' => '<p>Prinsip SOLID sangat krusial dalam pengembangan berorientasi objek. Kelima prinsip tersebut adalah: Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, dan Dependency Inversion.</p>',
                'status' => Post::STATUS_PENDING,
                'categories' => ['Teknologi & Coding'],
                'tags' => ['Laravel', 'Informatika'],
                'view_count' => 0,
            ],
        ];

        foreach ($postsData as $postInfo) {
            $author = $users->random();
            $reviewer = null;

            if ($postInfo['status'] === Post::STATUS_APPROVED) {
                // Find or make reviewer
                $reviewer = $users->where('id', '!=', $author->id)->first() ?? $author;
            }

            $post = Post::create([
                'user_id' => $author->id,
                'title' => $postInfo['title'],
                'summary' => $postInfo['summary'],
                'content' => $postInfo['content'],
                'status' => $postInfo['status'],
                'view_count' => $postInfo['view_count'],
                'reviewed_by_user_id' => $reviewer ? $reviewer->id : null,
                'reviewed_at' => $reviewer ? now()->subDays(rand(1, 10)) : null,
                'published_at' => $postInfo['status'] === Post::STATUS_APPROVED ? now()->subDays(rand(1, 10)) : null,
            ]);

            // Sync categories
            $postCatIds = [];
            foreach ($postInfo['categories'] as $catName) {
                if (isset($categories[$catName])) {
                    $postCatIds[] = $categories[$catName]->id;
                }
            }
            $post->categories()->sync($postCatIds);

            // Sync tags
            $postTagIds = [];
            foreach ($postInfo['tags'] as $tagName) {
                if (isset($tags[$tagName])) {
                    $postTagIds[] = $tags[$tagName]->id;
                }
            }
            $post->tags()->sync($postTagIds);
        }
    }
}
