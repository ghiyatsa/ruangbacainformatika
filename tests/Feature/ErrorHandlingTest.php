<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->withoutVite();

    Route::middleware('web')->get('/_test/errors/403', fn () => abort(403));
    Route::middleware('web')->get('/_test/errors/500', function () {
        throw new RuntimeException('Testing error page.');
    });
});

test('inertia requests render the shared error page for forbidden responses', function () {
    get('/_test/errors/403')
        ->assertForbidden()
        ->assertInertia(fn (Assert $page) => $page
            ->component('error-page')
            ->where('status', 403)
            ->has('auth')
            ->has('name'));
});

test('unknown pages render the shared not found page', function () {
    get('/halaman-acak-yang-tidak-ada')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('error-page')
            ->where('status', 404)
            ->has('auth')
            ->has('name'));
});

test('inertia requests render the shared error page for server errors', function () {
    get('/_test/errors/500')
        ->assertServerError()
        ->assertInertia(fn (Assert $page) => $page
            ->component('error-page')
            ->where('status', 500));
});
