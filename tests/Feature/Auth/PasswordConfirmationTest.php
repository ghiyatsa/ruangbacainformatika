<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)->get(route('password.confirm'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('auth/confirm-password'),
        );
});

it('password confirmation requires authentication', function () {
    get(route('password.confirm'))
        ->assertRedirect(route('login'));
});
