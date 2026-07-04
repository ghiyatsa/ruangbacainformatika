<?php

it('has a valid robots.txt file', function () {
    $filePath = public_path('robots.txt');
    expect(file_exists($filePath))->toBeTrue();

    $content = file_get_contents($filePath);
    expect($content)->toContain('User-agent: *')
        ->toContain('Disallow: /admin/')
        ->toContain('Disallow: /kiosk/')
        ->toContain('Disallow: /search')
        ->toContain('Sitemap: https://ruangbacainformatika.unimal.ac.id/sitemap.xml');
});
