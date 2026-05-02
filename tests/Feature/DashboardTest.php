<?php

use function Pest\Laravel\get;

it('dashboard starter route is no longer available', function () {
    get('/dashboard')->assertNotFound();
});
