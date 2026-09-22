<?php

use App\Models\Post;
use App\Models\StaticPage;
use App\Support\RichContentSanitizer;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;
use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

function sanitize(string $html): string
{
    return (string) app(RichContentSanitizer::class)->sanitize($html);
}

it('keeps paragraph alignment chosen in the rich editor', function () {
    expect(sanitize('<p style="text-align: center">Tengah</p>'))
        ->toBe('<p style="text-align: center">Tengah</p>');

    expect(sanitize('<h2 style="text-align: right">Judul</h2>'))
        ->toBe('<h2 style="text-align: right">Judul</h2>');
});

it('keeps only the alignment declaration and drops other style rules', function () {
    expect(sanitize('<p style="text-align: justify; color: red; font-size: 99px">x</p>'))
        ->toBe('<p style="text-align: justify">x</p>');
});

it('still strips dangerous style declarations', function () {
    $result = sanitize('<p style="position: fixed; background-image: url(javascript:alert(1))">x</p>');

    expect($result)->toBe('<p>x</p>');
});

it('keeps image sizing but drops risky image styles', function () {
    expect(sanitize('<img src="/x.jpg" style="width: 50%">'))
        ->toBe('<img src="/x.jpg" style="width: 50%" />');

    expect(sanitize('<img src="/x.jpg" style="width: 50%; position: fixed">'))
        ->toBe('<img src="/x.jpg" style="width: 50%" />');

    expect(sanitize('<img src="/x.jpg" style="width: url(javascript:alert(1))">'))
        ->toBe('<img src="/x.jpg" />');
});

it('renders the alignment in the article payload sent to the frontend', function () {
    $post = Post::factory()->published()->create([
        'content' => '<p style="text-align: center">Isi artikel rata tengah</p>',
    ]);

    get(route('posts.show', $post->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('post.data.content', fn (string $content) => str_contains($content, 'text-align: center')));
});

it('sanitizes system static pages before sending them to the frontend', function () {
    StaticPage::query()->updateOrCreate(['page_key' => 'about'], [
        'page_key' => 'about',
        'title' => 'Tentang',
        'slug' => 'about',
        'summary' => 'Ringkasan.',
        'content' => '<p>Isi</p><script>alert(1)</script><p style="text-align: center">Tengah</p>',
        'is_active' => true,
    ]);

    get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('pageContent.content', function (string $content): bool {
                return ! str_contains($content, '<script')
                    && str_contains($content, 'text-align: center');
            }));
});

it('sanitizes custom static pages before sending them to the frontend', function () {
    StaticPage::query()->create([
        'page_key' => null,
        'title' => 'Halaman Kustom',
        'slug' => 'halaman-kustom',
        'summary' => 'Ringkasan kustom.',
        'content' => '<p onclick="alert(1)">Isi kustom</p>',
        'is_active' => true,
    ]);

    get(route('pages.show', 'halaman-kustom'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('pageContent.content', fn (string $content): bool => ! str_contains($content, 'onclick')));
});
