<?php

namespace App\Support;

use App\Http\Middleware\HandleInertiaRequests;

/**
 * Menyimpan metadata SEO yang sedang aktif untuk request berjalan.
 *
 * Controller menyiapkan meta lewat {@see PageMeta} (mis. `forBook()`,
 * `forAcademicDocument()`). Nilai terakhir yang dihasilkan disimpan di sini
 * agar bisa dibagikan sebagai Inertia prop melalui
 * {@see HandleInertiaRequests::share()}.
 *
 * Tanpa ini, komponen React (SeoHead) tidak tahu og:image spesifik dokumen
 * dan selalu jatuh ke gambar OG generik situs — sehingga og:image per halaman
 * (buku/skripsi/tesis/laporan KP) tidak pernah terpakai saat dibagikan ke
 * media sosial.
 */
class SeoMetaState
{
    /**
     * @var array<string, mixed>
     */
    protected array $meta = [];

    /**
     * @param  array<string, mixed>  $meta
     */
    public function put(array $meta): void
    {
        $this->meta = $meta;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return $this->meta;
    }

    public function forget(): void
    {
        $this->meta = [];
    }
}
