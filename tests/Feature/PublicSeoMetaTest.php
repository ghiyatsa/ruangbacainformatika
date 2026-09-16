<?php

use App\Models\Book;
use App\Models\Setting;
use App\Models\Skripsi;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
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
    $response = get('/')->assertOk();

    assertResponseHasTag($response, [
        'name' => 'description',
        'content' => 'Deskripsi SEO kustom untuk pengujian halaman publik.',
    ]);
    assertResponseHasTag($response, [
        'name' => 'robots',
        'content' => 'noindex,nofollow',
    ]);
    assertResponseHasTag($response, [
        'name' => 'keywords',
        'content' => 'seo, katalog, ruang baca',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:title',
        'content' => 'Ruang Baca Custom',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image',
        'content' => route('og.site'),
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image:type',
        'content' => 'image/png',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image:width',
        'content' => '1200',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image:height',
        'content' => '1200',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:url',
        'content' => fn (string $value): bool => urlMatches($value, url('/')),
    ]);
    assertResponseHasTag($response, [
        'rel' => 'canonical',
        'href' => fn (string $value): bool => urlMatches($value, url('/')),
    ], 'link');
});

it('renders catalog-specific seo meta on the book detail page', function () {
    $book = Book::factory()->create([
        'title' => 'Pemrograman Web Lanjut',
        'slug' => 'pemrograman-web-lanjut',
        'description' => 'Panduan lengkap membangun aplikasi web modern dengan fokus pada performa, keamanan, dan pengalaman pengguna yang baik.',
    ]);

    $response = get(route('books.show', $book))->assertOk();

    assertResponseHasTag($response, [
        'name' => 'description',
        'content' => 'Panduan lengkap membangun aplikasi web modern dengan fokus pada performa, keamanan, dan pengalaman pengguna yang baik.',
    ]);
    assertResponseHasTag($response, [
        'name' => 'keywords',
        'content' => 'Pemrograman Web Lanjut, katalog buku, ruang baca informatika',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:title',
        'content' => 'Pemrograman Web Lanjut - Ruang Baca Custom',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image',
        'content' => route('og.books.show', $book),
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image:type',
        'content' => 'image/png',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image:width',
        'content' => '1200',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image:height',
        'content' => '600',
    ]);
    assertResponseHasTag($response, [
        'rel' => 'canonical',
        'href' => route('books.show', $book),
    ], 'link');
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

    $user = User::factory()->create([
        'email' => '230170001@mhs.unimal.ac.id',
        'is_approved' => true,
        'profile_completed_at' => now(),
    ]);
    Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
    $user->assignRole('member');

    $response = actingAs($user)
        ->get(route('skripsi.show', $skripsi))
        ->assertOk();

    assertResponseHasTag($response, [
        'name' => 'description',
        'content' => 'Penelitian ini membahas sistem rekomendasi koleksi perpustakaan berbasis perilaku peminjaman pengguna dan kemiripan topik.',
    ]);
    assertResponseHasTag($response, [
        'name' => 'keywords',
        'content' => 'Sistem Rekomendasi Perpustakaan, Nadia Putri, 2301700999, sistem rekomendasi, perpustakaan, text mining, skripsi informatika, ruang baca informatika',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:title',
        'content' => 'Sistem Rekomendasi Perpustakaan - Ruang Baca Custom',
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image',
        'content' => route('og.skripsi.show', $skripsi),
    ]);
    assertResponseHasTag($response, [
        'property' => 'og:image:type',
        'content' => 'image/png',
    ]);
    assertResponseHasTag($response, [
        'rel' => 'canonical',
        'href' => route('skripsi.show', $skripsi),
    ], 'link');
    assertResponseHasTag($response, [
        'name' => 'robots',
        'content' => 'noindex,nofollow',
    ]);
});
