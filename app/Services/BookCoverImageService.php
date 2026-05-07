<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class BookCoverImageService
{
    public function getDefaultCoverUrl(): string
    {
        return asset('images/book-cover-placeholder.svg');
    }

    public function storeFromUploadedFile(
        UploadedFile $file,
        string $directory = 'books/covers',
        string $disk = 'public',
        ?string $baseName = null,
    ): string {
        return $this->storeAsWebp($file->getRealPath(), $directory, $disk, $baseName);
    }

    public function storeFromUrl(
        string $url,
        string $directory = 'books/covers',
        string $disk = 'public',
        ?string $baseName = null,
    ): string {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'book-cover-');

        if ($temporaryPath === false) {
            throw new \RuntimeException('Temporary file could not be created.');
        }

        try {
            $response = Http::withUserAgent('Mozilla/5.0')
                ->timeout(15)
                ->throw()
                ->get($url);

            file_put_contents($temporaryPath, $response->body());

            return $this->storeAsWebp($temporaryPath, $directory, $disk, $baseName);
        } finally {
            if (file_exists($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    public function tryStoreFromUrl(
        string $url,
        string $directory = 'books/covers',
        string $disk = 'public',
        ?string $baseName = null,
    ): ?string {
        try {
            return $this->storeFromUrl($url, $directory, $disk, $baseName);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public function storeAsWebp(
        string $sourcePath,
        string $directory = 'books/covers',
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
            ->fit(Fit::Crop, 600, 800)
            ->save($targetPath);

        return $relativePath;
    }
}
