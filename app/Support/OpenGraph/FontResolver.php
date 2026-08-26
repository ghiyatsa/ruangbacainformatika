<?php

namespace App\Support\OpenGraph;

use Illuminate\Support\Str;

class FontResolver
{
    protected const FONT_DIRECTORY = 'fonts';

    protected static ?string $regular = null;

    protected static ?string $strong = null;

    public function path(bool $bold = false): ?string
    {
        if ($bold) {
            if (self::$strong === null) {
                self::$strong = $this->findFirstExistingPath($this->candidates(true));
            }

            return self::$strong;
        }

        if (self::$regular === null) {
            self::$regular = $this->findFirstExistingPath($this->candidates());
        }

        return self::$regular;
    }

    /**
     * @return array<int, string>
     */
    protected function candidates(bool $bold = false): array
    {
        return $bold
            ? [
                resource_path(self::FONT_DIRECTORY.'/Inter-Bold.ttf'),
                public_path(self::FONT_DIRECTORY.'/Inter-Bold.ttf'),
                'C:\\Windows\\Fonts\\arialbd.ttf',
                'C:\\Windows\\Fonts\\segoeuib.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
            ]
            : [
                resource_path(self::FONT_DIRECTORY.'/Inter-Regular.ttf'),
                public_path(self::FONT_DIRECTORY.'/Inter-Regular.ttf'),
                'C:\\Windows\\Fonts\\arial.ttf',
                'C:\\Windows\\Fonts\\segoeui.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/truetype/liberation2/LiberationSans-Regular.ttf',
            ];
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected function findFirstExistingPath(array $paths): ?string
    {
        foreach ($paths as $path) {
            if (! is_string($path) || ! $this->isPathAccessibleForRead($path)) {
                continue;
            }

            if (@is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function isPathAccessibleForRead(string $path, ?string $openBaseDir = null): bool
    {
        if ($path === '') {
            return false;
        }

        $openBaseDir ??= ini_get('open_basedir');

        if (! is_string($openBaseDir) || trim($openBaseDir) === '') {
            return true;
        }

        $normalizedPath = $this->normalizeComparablePath($path);

        foreach (explode(PATH_SEPARATOR, $openBaseDir) as $allowedPath) {
            $normalizedAllowedPath = $this->normalizeComparablePath($allowedPath);

            if ($normalizedAllowedPath === null) {
                continue;
            }

            if ($normalizedPath === $normalizedAllowedPath) {
                return true;
            }

            $allowedDirectory = rtrim($normalizedAllowedPath, '/');

            if ($allowedDirectory !== '' && Str::startsWith($normalizedPath, $allowedDirectory.'/')) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeComparablePath(string $path): ?string
    {
        $trimmedPath = trim($path);

        if ($trimmedPath === '') {
            return null;
        }

        $normalizedPath = str_replace('\\', '/', $trimmedPath);

        return DIRECTORY_SEPARATOR === '\\'
            ? Str::lower($normalizedPath)
            : $normalizedPath;
    }
}
