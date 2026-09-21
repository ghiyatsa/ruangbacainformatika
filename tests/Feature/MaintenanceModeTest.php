<?php

use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;
use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

/**
 * Maintenance mode menandai aplikasi lewat driver file (storage/framework/down),
 * jadi statusnya harus selalu dimatikan lagi walau asersi gagal — kalau tidak,
 * seluruh tes berikutnya akan menerima 503.
 */
function withMaintenanceMode(Closure $callback): void
{
    $mode = app()->maintenanceMode();

    $mode->activate([
        'except' => [],
        'redirect' => null,
        'retry' => null,
        'refresh' => null,
        'secret' => null,
        'status' => 503,
        'template' => null,
    ]);

    try {
        $callback();
    } finally {
        $mode->deactivate();
    }
}

it('maintenance mode renders the shared error page instead of the bare 503 screen', function () {
    withMaintenanceMode(function () {
        get(route('home'))
            ->assertStatus(503)
            ->assertInertia(fn (Assert $page) => $page
                ->component('error/index')
                ->where('status', 503));
    });
});

it('the maintenance copy says maintenance rather than service unavailable', function () {
    $source = file_get_contents(resource_path('js/pages/error/index.tsx'));

    expect($source)
        ->toContain('Sedang maintenance')
        ->and($source)->not->toContain('Layanan sementara tidak tersedia');
});

it('the standalone maintenance view keeps the shared error page styling', function () {
    $html = view('errors.503')->render();

    expect($html)
        ->toContain('Sedang maintenance')
        ->toContain('Informasi Kendala')
        ->toContain('Kembali ke beranda')
        ->not->toContain('Service Unavailable');
});

it('normal requests keep working once maintenance mode is lifted', function () {
    get(route('home'))->assertOk();
});
