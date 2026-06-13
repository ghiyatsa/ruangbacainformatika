<?php

use function Pest\Laravel\get;

it('redirects guest to login page when accessing dashboard', function () {
    get('/dashboard')->assertRedirect(route('login'));
});
