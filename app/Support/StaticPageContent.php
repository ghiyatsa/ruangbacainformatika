<?php

namespace App\Support;

use App\Models\StaticPage;
use App\Repositories\SettingRepository;
use Throwable;

class StaticPageContent
{
    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * @return array{summary: string, content: string}
     */
    public function about(): array
    {
        return $this->resolveSystemPage('about');
    }

    /**
     * @return array{summary: string, content: string}
     */
    public function privacyPolicy(): array
    {
        return $this->resolveSystemPage('privacy-policy');
    }

    /**
     * @return array{summary: string, content: string}
     */
    public function termsOfService(): array
    {
        return $this->resolveSystemPage('terms-of-service');
    }

    public function customPage(string $slug): ?StaticPage
    {
        return StaticPage::query()
            ->active()
            ->whereNull('page_key')
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return array<string, string>
     */
    public function values(): array
    {
        return once(function (): array {
            try {
                $values = $this->settingRepository->sectionValues('page_content', $this->defaults());
            } catch (Throwable) {
                $values = $this->defaults();
            }

            return [
                'about_summary' => $this->stringValue($values, 'about_summary', $this->defaults()['about_summary']),
                'about_content' => $this->stringValue($values, 'about_content', $this->defaults()['about_content']),
                'privacy_policy_summary' => $this->stringValue($values, 'privacy_policy_summary', $this->defaults()['privacy_policy_summary']),
                'privacy_policy_content' => $this->stringValue($values, 'privacy_policy_content', $this->defaults()['privacy_policy_content']),
                'terms_of_service_summary' => $this->stringValue($values, 'terms_of_service_summary', $this->defaults()['terms_of_service_summary']),
                'terms_of_service_content' => $this->stringValue($values, 'terms_of_service_content', $this->defaults()['terms_of_service_content']),
            ];
        });
    }

    /**
     * @return array<string, string>
     */
    protected function defaults(): array
    {
        return [
            'about_summary' => 'Informasi singkat tentang Ruang Baca Teknik Informatika Universitas Malikussaleh.',
            'about_content' => <<<'HTML'
<h2>Ruang Baca yang Lebih Mudah Diakses</h2>
<p>Ruang Baca Teknik Informatika membantu mahasiswa dan dosen mencari buku, melihat informasi layanan, dan memakai fasilitas ruang baca dengan lebih mudah.</p>
<h3>Layanan utama</h3>
<ul>
    <li><strong>Katalog terpadu (OPAC).</strong> Pencarian online untuk buku, laporan kerja praktik, skripsi, tesis, dan dokumen lain yang tersedia di ruang baca.</li>
    <li><strong>Kiosk mandiri.</strong> Antarmuka kiosk di lokasi untuk pencatatan kunjungan, pendaftaran anggota, serta proses peminjaman dan pengembalian secara mandiri.</li>
    <li><strong>Sirkulasi dan pengajuan online.</strong> Pengguna terdaftar dapat mengajukan peminjaman buku, memantau batas pengembalian, dan meninjau riwayat layanan melalui akun masing-masing.</li>
    <li><strong>Pemeriksaan kemiripan dokumen.</strong> Fitur pemeriksaan awal untuk melihat kemiripan judul atau dokumen sebelum diajukan.</li>
</ul>
HTML,
            'privacy_policy_summary' => 'Ringkasan penggunaan dan perlindungan data pengguna di Ruang Baca Teknik Informatika.',
            'privacy_policy_content' => <<<'HTML'
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
HTML,
            'terms_of_service_summary' => 'Ketentuan penggunaan layanan Ruang Baca Teknik Informatika.',
            'terms_of_service_content' => <<<'HTML'
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
HTML,
        ];
    }

    /**
     * @return array{summary: string, content: string}
     */
    protected function resolveSystemPage(string $pageKey): array
    {
        $page = StaticPage::query()
            ->active()
            ->where('page_key', $pageKey)
            ->first();

        if ($page instanceof StaticPage) {
            return [
                'summary' => trim($page->summary),
                'content' => trim($page->content),
            ];
        }

        $values = $this->values();

        return match ($pageKey) {
            'about' => [
                'summary' => $values['about_summary'],
                'content' => $values['about_content'],
            ],
            'privacy-policy' => [
                'summary' => $values['privacy_policy_summary'],
                'content' => $values['privacy_policy_content'],
            ],
            'terms-of-service' => [
                'summary' => $values['terms_of_service_summary'],
                'content' => $values['terms_of_service_content'],
            ],
            default => [
                'summary' => '',
                'content' => '',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $values
     */
    protected function stringValue(array $values, string $key, string $default = ''): string
    {
        $value = $values[$key] ?? $default;

        return is_string($value) && trim($value) !== '' ? trim($value) : $default;
    }
}
