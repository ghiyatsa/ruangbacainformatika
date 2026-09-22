<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Post;

/**
 * Varian ringan PostResource untuk daftar/kartu artikel.
 *
 * Kartu (index, artikel terkait, populer, terbaru di beranda) hanya menampilkan
 * judul, excerpt, cover, dan meta — tidak pernah merender badan artikel. Jadi
 * HTML penuh tiap artikel tidak perlu dikirim, dan halaman listing jadi jauh
 * lebih ringan.
 *
 * @mixin Post
 */
class PostListResource extends PostResource
{
    protected bool $includeContent = false;
}
