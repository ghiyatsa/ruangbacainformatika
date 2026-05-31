<?php

use function Pest\Laravel\get;

it('responds successfully on the built-in health endpoint', function () {
    get('/up')
        ->assertOk();
});
