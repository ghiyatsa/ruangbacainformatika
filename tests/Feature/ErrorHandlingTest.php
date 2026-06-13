<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;
use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();

    Route::middleware('web')->get('/_test/errors/403', fn () => abort(403));
    Route::middleware('web')->get('/_test/errors/500', function () {
        throw new RuntimeException('Testing error page.');
    });
});

it('inertia requests render the shared error page for forbidden responses', function () {
    get('/_test/errors/403')
        ->assertForbidden()
        ->assertInertia(fn (Assert $page) => $page
            ->component('error/index')
            ->where('status', 403)
            ->has('auth')
            ->has('name'));
});

it('unknown pages render the shared not found page', function () {
    get('/halaman-acak-yang-tidak-ada')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('error/index')
            ->where('status', 404)
            ->has('auth')
            ->has('name'));
});

it('inertia requests render the shared error page for server errors', function () {
    get('/_test/errors/500')
        ->assertServerError()
        ->assertInertia(fn (Assert $page) => $page
            ->component('error/index')
            ->where('status', 500));
});
