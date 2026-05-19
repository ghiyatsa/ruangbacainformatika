<?php

use function Pest\Laravel\get;

it('example', function () {
    $response = get('/');

    $response->assertStatus(200);
});
