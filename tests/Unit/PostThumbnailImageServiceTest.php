<?php

use App\Services\PostThumbnailImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Image;

it('gets default thumbnail url', function () {
    $service = new PostThumbnailImageService;
    expect($service->getDefaultThumbnailUrl())->toBe(asset('images/article-placeholder.svg'));
});

it('stores and resizes an uploaded file as webp in 16:9 ratio', function () {
    Storage::fake('public');

    // Create a temporary dummy image (e.g. 100x100 square)
    $imageFile = UploadedFile::fake()->image('test_cover.jpg', 100, 100);

    $service = new PostThumbnailImageService;
    $path = $service->storeFromUploadedFile($imageFile, 'posts/covers', 'public', 'my-awesome-post');

    expect($path)->toStartWith('posts/covers/my-awesome-post-')
        ->and($path)->toEndWith('.webp');

    Storage::disk('public')->assertExists($path);

    // Let's load the saved image and check the dimensions are exactly 1280x720 (16:9)
    $fullPath = Storage::disk('public')->path($path);
    $savedImage = Image::load($fullPath);
    expect($savedImage->getWidth())->toBe(1280)
        ->and($savedImage->getHeight())->toBe(720);
});
