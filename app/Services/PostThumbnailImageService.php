<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class PostThumbnailImageService
{
    /**
     * Get default thumbnail URL.
     */
    public function getDefaultThumbnailUrl(): string
    {
        return asset('images/article-placeholder.svg');
    }

    /**
     * Store from uploaded file.
     */
    public function storeFromUploadedFile(
        UploadedFile $file,
        string $directory = 'posts/covers',
        string $disk = 'public',
        ?string $baseName = null,
    ): string {
        return $this->storeAsWebp($file->getRealPath(), $directory, $disk, $baseName);
    }

    /**
     * Store as webp.
     */
    public function storeAsWebp(
        string $sourcePath,
        string $directory = 'posts/covers',
        string $disk = 'public',
        ?string $baseName = null,
    ): string {
        $name = $baseName
            ? Str::slug($baseName).'-'.Str::lower(Str::random(8))
            : Str::random(40);

        $relativePath = trim($directory, '/').'/'.$name.'.webp';
        $targetPath = Storage::disk($disk)->path($relativePath);

        Storage::disk($disk)->makeDirectory(trim($directory, '/'));

        Image::load($sourcePath)
            ->fit(Fit::Crop, 1280, 720)
            ->save($targetPath);

        return $relativePath;
    }
}
