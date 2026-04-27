<?php

test('dashboard starter route is no longer available', function () {
    $this->get('/dashboard')->assertNotFound();
});
