<?php

use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('about page is displayed', function () {
    get(route('about'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('about'),
        );
});

it('contact page is displayed', function () {
    get(route('contact'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('contact'),
        );
});
