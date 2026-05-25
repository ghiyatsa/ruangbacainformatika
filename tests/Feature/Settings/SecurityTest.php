<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;

it('security page is displayed', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->get(route('settings.security.edit'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('settings/security')
                ->has('sessions'),
        );
});

it('security page is accessible without password confirmation', function () {
    $user = User::factory()->create();

    /** @var User $user */
    actingAs($user)
        ->get(route('settings.security.edit'))
        ->assertOk();
});
