<?php

use App\Support\OpenGraph\FontResolver;

it('ignores font candidates outside open basedir restrictions', function () {
    $resolver = new class extends FontResolver
    {
        public function pathAccessibleForRead(string $path, ?string $openBaseDir = null): bool
        {
            return $this->isPathAccessibleForRead($path, $openBaseDir);
        }
    };

    $allowedDirectory = base_path();
    $blockedPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
    $allowedPath = base_path('composer.json');

    expect($resolver->pathAccessibleForRead($blockedPath, $allowedDirectory))->toBeFalse()
        ->and($resolver->pathAccessibleForRead($allowedPath, $allowedDirectory))->toBeTrue();
});
