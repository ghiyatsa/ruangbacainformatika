<?php

use App\Support\OpenGraphImage;
use App\Support\SiteSettings;

it('ignores font candidates outside open basedir restrictions', function () {
    $image = new class(app(SiteSettings::class)) extends OpenGraphImage
    {
        public function pathAccessibleForRead(string $path, ?string $openBaseDir = null): bool
        {
            return $this->isPathAccessibleForRead($path, $openBaseDir);
        }
    };

    $allowedDirectory = base_path();
    $blockedPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
    $allowedPath = base_path('composer.json');

    expect($image->pathAccessibleForRead($blockedPath, $allowedDirectory))->toBeFalse()
        ->and($image->pathAccessibleForRead($allowedPath, $allowedDirectory))->toBeTrue();
});
