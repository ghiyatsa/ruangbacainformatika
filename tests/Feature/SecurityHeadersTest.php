<?php

test('web responses include baseline security headers', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader(
            'Permissions-Policy',
            'camera=(), geolocation=(), microphone=()',
        );
});
