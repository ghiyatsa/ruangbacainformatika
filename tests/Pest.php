<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Safety Guard
|--------------------------------------------------------------------------
|
| A cached config file (from `php artisan config:cache`) freezes the values
| that were present when it was generated, which silently overrides every
| `<env>` entry in phpunit.xml. When that happens the suite connects to the
| real local MySQL database instead of the in-memory SQLite instance and
| `RefreshDatabase` wipes development data. Fail loudly instead of
| corrupting anyone's database.
|
*/

beforeAll(function (): void {
    if (app()->configurationIsCached()) {
        throw new RuntimeException(
            'Configuration is cached. Run `php artisan config:clear` before testing, '
            .'otherwise phpunit.xml environment values are ignored and the suite '
            .'may connect to your local database.'
        );
    }
});

beforeEach(function (): void {
    if (config('database.default') !== 'sqlite') {
        throw new RuntimeException(
            'Tests must run against SQLite. Got database connection '
            .'"'.config('database.default').'". Check that phpunit.xml env values '
            .'are applied and no configuration cache is present.'
        );
    }
});

/**
 * Helper autentikasi user untuk pengujian Feature.
 */
function asUser(?User $user = null): TestCase
{
    return test()->actingAs($user ?? User::factory()->create());
}

function asMember(?User $user = null): TestCase
{
    return test()->actingAs($user ?? User::factory()->member()->create());
}

function asStaff(?User $user = null): TestCase
{
    return test()->actingAs($user ?? User::factory()->staff()->create());
}

function asAdmin(?User $user = null): TestCase
{
    return test()->actingAs($user ?? User::factory()->admin()->create());
}

/*
|--------------------------------------------------------------------------
| Meta Tag Assertions
|--------------------------------------------------------------------------
|
| Tag meta dirender lewat komponen `<x-inertia::head>`, yang menambahkan
| atribut `data-inertia` dan memindahkannya ke urutan lain pada markup akhir.
| Akibatnya pencocokan string kaku seperti `property="og:url" content="..."`
| gagal walaupun nilainya benar.
|
| Helper di bawah mencocokkan atribut per-tag tanpa peduli urutan atau
| atribut tambahan, sehingga tahan terhadap perubahan urutan atribut.
|
*/

/**
 * Bandingkan dua URL dengan mengabaikan perbedaan trailing slash pada root.
 *
 * `url('/')` menghasilkan "https://contoh.test" tanpa slash, sementara tag
 * canonical/og:url boleh dirender sebagai "https://contoh.test/". Keduanya
 * menunjuk alamat yang sama.
 */
function urlMatches(string $actual, string $expected): bool
{
    $normalize = static function (string $url): string {
        $trimmed = rtrim($url, '/');

        return $trimmed === '' ? '/' : $trimmed;
    };

    return $normalize($actual) === $normalize($expected);
}

/**
 * Cari tag HTML yang memuat semua atribut yang diminta (urutan bebas).
 *
 * Setiap nilai atribut boleh berupa string (dicocokkan persis) atau
 * callable yang menerima nilai atribut dan mengembalikan bool.
 *
 * @param  array<string, string|callable(string): bool>  $attributes
 */
function htmlHasTagWithAttributes(string $html, array $attributes, ?string $tag = null): bool
{
    $tagPattern = $tag !== null ? preg_quote($tag, '/') : '[a-z][a-z0-9:-]*';

    if (! preg_match_all('/<'.$tagPattern.'\b[^>]*>/i', $html, $matches)) {
        return false;
    }

    foreach ($matches[0] as $tagHtml) {
        $matched = true;

        foreach ($attributes as $name => $expected) {
            $pattern = '/(?<=[\s])'.preg_quote($name, '/').'\s*=\s*"([^"]*)"/i';

            if (! preg_match($pattern, $tagHtml, $attrMatch)) {
                $matched = false;
                break;
            }

            $actual = html_entity_decode($attrMatch[1], ENT_QUOTES | ENT_HTML5);

            $ok = is_callable($expected) ? $expected($actual) : $actual === $expected;

            if (! $ok) {
                $matched = false;
                break;
            }
        }

        if ($matched) {
            return true;
        }
    }

    return false;
}

/**
 * Assert bahwa respons memuat tag dengan kombinasi atribut yang diminta.
 *
 * @param  array<string, string|callable(string): bool>  $attributes
 */
function assertResponseHasTag(TestResponse $response, array $attributes, ?string $tag = null): void
{
    $html = $response->getContent();

    expect($html)->toBeString()
        ->and(htmlHasTagWithAttributes((string) $html, $attributes, $tag))->toBeTrue(
            'Gagal menemukan tag dengan atribut: '.json_encode(array_keys($attributes))
        );
}
