<?php

use Inertia\Testing\AssertableInertia as Assert;

test('web responses include baseline security headers', function () {
    $response = $this->get(route('home'));
    $contentSecurityPolicy = $response->headers->get('Content-Security-Policy');

    $response->assertOk()
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Content-Security-Policy')
        ->assertHeader(
            'Permissions-Policy',
            'camera=(), geolocation=(), microphone=()',
        );

    expect($contentSecurityPolicy)
        ->not->toBeNull()
        ->toContain("default-src 'self'")
        ->toContain("object-src 'none'")
        ->toContain('https://accounts.google.com')
        ->toContain('https://accounts.google.com/gsi/style')
        ->toContain('https://www.googleapis.com')
        ->toContain("'nonce-");
});

test('kiosk responses allow camera access for the same origin only', function () {
    $response = $this->get(route('kiosk.index'));

    $response->assertOk()
        ->assertHeader(
            'Permissions-Policy',
            'camera=(self), geolocation=(), microphone=()',
        );
});

test('lighthouse requests disable google one tap while keeping google login configured', function () {
    config()->set('services.google.client_id', 'google-client-id');
    config()->set('services.google.client_secret', 'google-client-secret');
    config()->set('services.google.redirect', 'https://ruangbacainformatika.unimal.ac.id/auth/google/callback');

    $this->withHeader('User-Agent', 'Mozilla/5.0 Chrome-Lighthouse')
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('googleAuth.enabled', true)
            ->where('googleAuth.oneTapEnabled', false)
        );
});
