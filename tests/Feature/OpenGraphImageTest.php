<?php

use App\Models\Book;
use App\Models\Setting;
use App\Models\Skripsi;
use App\Support\OpenGraphImage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\get;

function assertPngOpenGraphResponse(TestResponse $response): void
{
    assertPngOpenGraphResponseWithSize($response, OpenGraphImage::DETAIL_WIDTH, OpenGraphImage::DETAIL_HEIGHT);
}

function assertPngOpenGraphResponseWithSize(TestResponse $response, int $expectedWidth, int $expectedHeight): void
{
    $response->assertOk()
        ->assertHeader('Content-Type', OpenGraphImage::MIME_TYPE);

    $binary = $response->getContent();

    expect(substr($binary, 0, 8))->toBe("\x89PNG\x0D\x0A\x1A\x0A");

    $image = imagecreatefromstring($binary);

    expect($image)->toBeInstanceOf(GdImage::class)
        ->and(imagesx($image))->toBe($expectedWidth)
        ->and(imagesy($image))->toBe($expectedHeight);

    imagedestroy($image);
}

it('renders the site open graph image with the configured logo-driven identity', function () {
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_name'],
        ['value' => 'Ruang Baca Custom'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_tagline'],
        ['value' => 'Portal koleksi dan layanan digital'],
    );

    assertPngOpenGraphResponseWithSize(get(route('og.site')), OpenGraphImage::SITE_WIDTH, OpenGraphImage::SITE_HEIGHT);
});

it('renders a catalog detail open graph image with title author and logo', function () {
    $book = Book::factory()->create([
        'title' => 'Clean Code untuk Pengembangan Sistem Perpustakaan',
        'slug' => 'clean-code-perpustakaan',
    ]);
    $book->authors()->create(['name' => 'Robert Martin', 'slug' => 'robert-martin']);

    assertPngOpenGraphResponse(get(route('og.books.show', $book)));
});

it('renders an academic document open graph image without requiring a cover', function () {
    Queue::fake();

    $skripsi = Skripsi::factory()->create([
        'title' => 'Sistem Rekomendasi Perpustakaan Berbasis Topik',
        'author_name' => 'Nadia Putri',
        'student_id' => '2301700999',
    ]);

    assertPngOpenGraphResponse(get(route('og.skripsi.show', $skripsi)));
});
