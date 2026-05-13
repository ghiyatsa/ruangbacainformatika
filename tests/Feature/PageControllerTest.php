<?php

use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('about page is displayed', function () {
    get(route('about'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('about')
                ->where('site.contactEmail', 'informatika@unimal.ac.id')
                ->where('site.department', 'Program Studi Teknik Informatika Universitas Malikussaleh'),
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
            fn (Assert $page) => $page->component('privacy-policy'),
        );
});

it('terms of service page is displayed', function () {
    get(route('terms-of-service'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('terms-of-service'),
        );
});
