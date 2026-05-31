<?php

use App\Models\StaticPage;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('about page is displayed', function () {
    get(route('about'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('about')
                ->where('pageContent.summary', 'Informasi singkat tentang Ruang Baca Teknik Informatika Universitas Malikussaleh.')
                ->where('pageContent.content', fn (string $content) => str_contains($content, 'Katalog terpadu'))
                ->where('site.contactEmail', 'informatika@unimal.ac.id')
                ->where('site.department', 'Program Studi Teknik Informatika Universitas Malikussaleh'),
        );
});

it('about team page is displayed', function () {
    get(route('about-team'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('about-team')
                ->where('site.contactEmail', 'informatika@unimal.ac.id'),
        );
});

it('contact page is displayed', function () {
    get(route('contact'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('contact')
                ->where('site.address', 'Jl. Cot Tengku Nie, Reuleut, Aceh Utara 24355'),
        );
});

it('privacy policy page is displayed', function () {
    get(route('privacy-policy'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('privacy-policy')
                ->where('pageContent.summary', 'Ringkasan penggunaan dan perlindungan data pengguna di Ruang Baca Teknik Informatika.')
                ->where('pageContent.content', fn (string $content) => str_contains($content, 'Hak pengguna')),
        );
});

it('terms of service page is displayed', function () {
    get(route('terms-of-service'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('terms-of-service')
                ->where('pageContent.summary', 'Ketentuan penggunaan layanan Ruang Baca Teknik Informatika.')
                ->where('pageContent.content', fn (string $content) => str_contains($content, 'Ketentuan penggunaan layanan')),
        );
});

it('system static pages use the matching static page record when available', function () {
    StaticPage::query()->updateOrCreate([
        'page_key' => 'about',
    ], [
        'page_key' => 'about',
        'title' => 'Tentang Layanan',
        'slug' => 'about',
        'summary' => 'Ringkasan about dari resource.',
        'content' => '<h2>About Resource</h2><p>Isi about dari resource.</p>',
        'is_active' => true,
    ]);

    get(route('about'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('about')
                ->where('pageContent.summary', 'Ringkasan about dari resource.')
                ->where('pageContent.content', '<h2>About Resource</h2><p>Isi about dari resource.</p>'),
        );
});
