<?php

use App\Models\Book;
use App\Models\Setting;
use App\Models\Skripsi;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\get;

beforeEach(function () {
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_name'],
        ['value' => 'Ruang Baca Custom'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_description'],
        ['value' => 'Deskripsi SEO kustom untuk pengujian halaman publik.'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_keywords'],
        ['value' => 'seo, katalog, ruang baca'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'seo_robots'],
        ['value' => 'noindex,nofollow'],
    );
});

it('renders configured seo meta on the welcome page', function () {
    get('/')
        ->assertOk()
        ->assertSee('name="description" content="Daftar buku dan arsip akademik Ruang Baca Teknik Informatika Universitas Malikussaleh."', false)
        ->assertSee('name="robots" content="noindex,nofollow"', false)
        ->assertSee('name="keywords" content="seo, katalog, ruang baca"', false)
        ->assertSee('property="og:title" content="Ruang Baca Custom"', false)
        ->assertSee('property="og:image" content="'.route('og.site').'"', false)
        ->assertSee('property="og:image:type" content="image/png"', false)
        ->assertSee('property="og:image:width" content="1200"', false)
        ->assertSee('property="og:image:height" content="1200"', false)
        ->assertSee('property="og:url" content="'.url('/').'"', false)
        ->assertSee('rel="canonical" href="'.url('/').'"', false);
});

it('renders catalog-specific seo meta on the book detail page', function () {
    $book = Book::factory()->create([
        'title' => 'Pemrograman Web Lanjut',
        'slug' => 'pemrograman-web-lanjut',
        'description' => 'Panduan lengkap membangun aplikasi web modern dengan fokus pada performa, keamanan, dan pengalaman pengguna yang baik.',
    ]);

    get(route('books.show', $book))
        ->assertOk()
        ->assertSee('name="description" content="Panduan lengkap membangun aplikasi web modern dengan fokus pada performa, keamanan, dan pengalaman pengguna yang baik."', false)
        ->assertSee('name="keywords" content="Pemrograman Web Lanjut, katalog buku, ruang baca informatika"', false)
        ->assertSee('property="og:title" content="Pemrograman Web Lanjut - Ruang Baca Custom"', false)
        ->assertSee('property="og:image" content="'.route('og.books.show', $book).'"', false)
        ->assertSee('property="og:image:type" content="image/png"', false)
        ->assertSee('property="og:image:width" content="1200"', false)
        ->assertSee('property="og:image:height" content="600"', false)
        ->assertSee('rel="canonical" href="'.route('books.show', $book).'"', false);
});

it('renders catalog-specific seo meta on the skripsi detail page', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create([
        'title' => 'Sistem Rekomendasi Perpustakaan',
        'author_name' => 'Nadia Putri',
        'student_id' => '2301700999',
        'abstract' => 'Penelitian ini membahas sistem rekomendasi koleksi perpustakaan berbasis perilaku peminjaman pengguna dan kemiripan topik.',
        'keywords' => 'sistem rekomendasi, perpustakaan, text mining',
    ]);

    get(route('skripsi.show', $skripsi))
        ->assertOk()
        ->assertSee('name="description" content="Penelitian ini membahas sistem rekomendasi koleksi perpustakaan berbasis perilaku peminjaman pengguna dan kemiripan topik."', false)
        ->assertSee('name="keywords" content="Sistem Rekomendasi Perpustakaan, Nadia Putri, 2301700999, sistem rekomendasi, perpustakaan, text mining, skripsi informatika, ruang baca informatika"', false)
        ->assertSee('property="og:title" content="Sistem Rekomendasi Perpustakaan - Ruang Baca Custom"', false)
        ->assertSee('property="og:image" content="'.route('og.skripsi.show', $skripsi).'"', false)
        ->assertSee('property="og:image:type" content="image/png"', false)
        ->assertSee('rel="canonical" href="'.route('skripsi.show', $skripsi).'"', false)
        ->assertSee('name="robots" content="noindex,nofollow"', false);
});
