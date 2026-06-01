<?php

namespace App\Support;

use App\Models\Book;
use GdImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OpenGraphImage
{
    protected const FONT_DIRECTORY = 'fonts';

    public const MIME_TYPE = 'image/png';

    public const WIDTH = 1200;

    public const HEIGHT = 600;

    public function __construct(
        protected SiteSettings $siteSettings,
    ) {}

    /**
     * @return array{ogImage:string,ogImageType:string,ogImageWidth:int,ogImageHeight:int}
     */
    public function defaultMeta(): array
    {
        return [
            'ogImage' => route('og.site'),
            'ogImageType' => self::MIME_TYPE,
            'ogImageWidth' => self::WIDTH,
            'ogImageHeight' => self::HEIGHT,
        ];
    }

    /**
     * @return array{ogImage:string,ogImageType:string,ogImageWidth:int,ogImageHeight:int}
     */
    public function bookMeta(Book $book): array
    {
        return [
            'ogImage' => route('og.books.show', $book),
            'ogImageType' => self::MIME_TYPE,
            'ogImageWidth' => self::WIDTH,
            'ogImageHeight' => self::HEIGHT,
        ];
    }

    /**
     * @return array{ogImage:string,ogImageType:string,ogImageWidth:int,ogImageHeight:int}
     */
    public function academicDocumentMeta(string $routeName, Model $document): array
    {
        return [
            'ogImage' => route($routeName, $document),
            'ogImageType' => self::MIME_TYPE,
            'ogImageWidth' => self::WIDTH,
            'ogImageHeight' => self::HEIGHT,
        ];
    }

    public function renderSite(): string
    {
        $settings = $this->siteSettings->values();

        return $this->renderPng(function (GdImage $image) use ($settings): void {
            $colors = $this->palette($image, $settings['theme_color']);

            imagefilledrectangle($image, 0, 0, self::WIDTH, self::HEIGHT, $colors['slate50']);
            imagefilledrectangle($image, 0, self::HEIGHT - 16, self::WIDTH, self::HEIGHT, $colors['theme']);

            $this->drawRoundedRectangle($image, 72, 72, 1128, 528, 36, $colors['white'], $colors['slate200']);
            $this->drawLogoCard($image, 96, 96, 152, 152, $colors);

            $this->drawTextLine($image, $settings['site_name'], 96, 250, 64, $colors['slate900'], true);
            $this->drawTextLine($image, $settings['site_tagline'], 96, 338, 28, $colors['slate600']);
        });
    }

    public function renderCatalogDetail(
        string $label,
        string $title,
        string $author,
    ): string {
        $settings = $this->siteSettings->values();

        return $this->renderPng(function (GdImage $image) use ($label, $title, $author, $settings): void {
            $colors = $this->palette($image, $settings['theme_color']);

            imagefilledrectangle($image, 0, 0, self::WIDTH, self::HEIGHT, $colors['slate50']);
            imagefilledrectangle($image, 0, self::HEIGHT - 16, self::WIDTH, self::HEIGHT, $colors['theme']);

            $this->drawRoundedRectangle($image, 72, 72, 1128, 528, 36, $colors['white'], $colors['slate200']);
            $this->drawRoundedRectangle($image, 96, 96, 266, 134, 19, $colors['slate200']);
            $this->drawCenteredTextLine($image, $label, 181, 120, 18, $colors['slate700'], true);

            $titleLines = $this->wrapText($title, 28, 3);
            foreach ($titleLines as $index => $line) {
                $this->drawTextLine($image, $line, 96, 198 + ($index * 64), 48, $colors['slate900'], true);
            }

            $authorLines = $this->wrapText($author, 38, 2);
            foreach ($authorLines as $index => $line) {
                $this->drawTextLine($image, $line, 96, 430 + ($index * 34), 26, $colors['slate600']);
            }

            $this->drawTextLine($image, $settings['site_name'], 96, 520, 22, $colors['slate400'], true);
            $this->drawRoundedRectangle($image, 904, 96, 1104, 376, 28, $colors['slate50'], $colors['slate200']);
            $this->drawLogo($image, 904, 136, 200, 200, $colors);
        });
    }

    protected function renderPng(callable $render): string
    {
        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $binary = null;

        if (! $image instanceof GdImage) {
            throw new RuntimeException('Failed to initialize the open graph image canvas.');
        }

        imageantialias($image, true);
        imagesavealpha($image, true);
        imagealphablending($image, true);

        try {
            $render($image);

            ob_start();
            imagepng($image);
            $binary = ob_get_clean();
        } finally {
            imagedestroy($image);
        }

        if (! is_string($binary)) {
            throw new RuntimeException('Failed to encode the open graph image.');
        }

        return $binary;
    }

    /**
     * @return array<string, int>
     */
    protected function palette(GdImage $image, string $themeColor): array
    {
        return [
            'theme' => $this->allocateColor($image, $themeColor),
            'white' => $this->allocateColor($image, '#FFFFFF'),
            'slate50' => $this->allocateColor($image, '#F8FAFC'),
            'slate200' => $this->allocateColor($image, '#E2E8F0'),
            'slate400' => $this->allocateColor($image, '#94A3B8'),
            'slate600' => $this->allocateColor($image, '#475569'),
            'slate700' => $this->allocateColor($image, '#334155'),
            'slate900' => $this->allocateColor($image, '#0F172A'),
        ];
    }

    protected function allocateColor(GdImage $image, string $hex): int
    {
        $normalized = ltrim($hex, '#');
        $red = hexdec(substr($normalized, 0, 2));
        $green = hexdec(substr($normalized, 2, 2));
        $blue = hexdec(substr($normalized, 4, 2));

        return imagecolorallocate($image, $red, $green, $blue);
    }

    protected function drawRoundedRectangle(
        GdImage $image,
        int $left,
        int $top,
        int $right,
        int $bottom,
        int $radius,
        int $fillColor,
        ?int $borderColor = null,
    ): void {
        imagefilledrectangle($image, $left + $radius, $top, $right - $radius, $bottom, $fillColor);
        imagefilledrectangle($image, $left, $top + $radius, $right, $bottom - $radius, $fillColor);
        imagefilledellipse($image, $left + $radius, $top + $radius, $radius * 2, $radius * 2, $fillColor);
        imagefilledellipse($image, $right - $radius, $top + $radius, $radius * 2, $radius * 2, $fillColor);
        imagefilledellipse($image, $left + $radius, $bottom - $radius, $radius * 2, $radius * 2, $fillColor);
        imagefilledellipse($image, $right - $radius, $bottom - $radius, $radius * 2, $radius * 2, $fillColor);

        if ($borderColor === null) {
            return;
        }

        imageline($image, $left + $radius, $top, $right - $radius, $top, $borderColor);
        imageline($image, $left + $radius, $bottom, $right - $radius, $bottom, $borderColor);
        imageline($image, $left, $top + $radius, $left, $bottom - $radius, $borderColor);
        imageline($image, $right, $top + $radius, $right, $bottom - $radius, $borderColor);
        imagearc($image, $left + $radius, $top + $radius, $radius * 2, $radius * 2, 180, 270, $borderColor);
        imagearc($image, $right - $radius, $top + $radius, $radius * 2, $radius * 2, 270, 360, $borderColor);
        imagearc($image, $left + $radius, $bottom - $radius, $radius * 2, $radius * 2, 90, 180, $borderColor);
        imagearc($image, $right - $radius, $bottom - $radius, $radius * 2, $radius * 2, 0, 90, $borderColor);
    }

    /**
     * @param  array<string, int>  $colors
     */
    protected function drawLogoCard(GdImage $image, int $x, int $y, int $width, int $height, array $colors): void
    {
        $this->drawRoundedRectangle($image, $x, $y, $x + $width, $y + $height, 32, $colors['white']);
        $this->drawLogo($image, $x, $y, $width, $height, $colors);
    }

    /**
     * @param  array<string, int>  $colors
     */
    protected function drawLogo(GdImage $image, int $x, int $y, int $width, int $height, array $colors): void
    {
        $logo = $this->resolveSiteLogoImage();

        if ($logo instanceof GdImage) {
            $this->copyCenteredImage($image, $logo, $x, $y, $width, $height);
            imagedestroy($logo);

            return;
        }

        $this->drawRoundedRectangle($image, $x, $y, $x + $width, $y + $height, 32, $colors['slate200']);
        $this->drawCenteredTextLine($image, $this->siteInitials(), $x + (int) floor($width / 2), $y + (int) floor($height * 0.61), 52, $colors['slate900'], true);
    }

    protected function copyCenteredImage(
        GdImage $destination,
        GdImage $source,
        int $x,
        int $y,
        int $width,
        int $height,
    ): void {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth < 1 || $sourceHeight < 1) {
            return;
        }

        $scale = min($width / $sourceWidth, $height / $sourceHeight);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $targetX = $x + (int) floor(($width - $targetWidth) / 2);
        $targetY = $y + (int) floor(($height - $targetHeight) / 2);

        imagecopyresampled($destination, $source, $targetX, $targetY, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
    }

    protected function drawTextLine(
        GdImage $image,
        string $text,
        int $x,
        int $baselineY,
        int $size,
        int $color,
        bool $bold = false,
    ): void {
        $fontPath = $this->fontPath($bold);

        if ($fontPath !== null) {
            imagettftext($image, $size, 0, $x, $baselineY, $color, $fontPath, $text);

            return;
        }

        imagestring($image, 5, $x, max(0, $baselineY - 20), Str::limit($text, 80), $color);
    }

    protected function drawCenteredTextLine(
        GdImage $image,
        string $text,
        int $centerX,
        int $baselineY,
        int $size,
        int $color,
        bool $bold = false,
    ): void {
        $fontPath = $this->fontPath($bold);

        if ($fontPath !== null) {
            $box = imagettfbbox($size, 0, $fontPath, $text);

            if (is_array($box)) {
                $width = (int) abs($box[4] - $box[0]);
                $this->drawTextLine($image, $text, $centerX - (int) floor($width / 2), $baselineY, $size, $color, $bold);

                return;
            }
        }

        $fallbackX = $centerX - (int) floor((imagefontwidth(5) * strlen($text)) / 2);
        imagestring($image, 5, max(0, $fallbackX), max(0, $baselineY - 20), Str::limit($text, 40), $color);
    }

    protected function fontPath(bool $bold = false): ?string
    {
        static $regular = null;
        static $strong = null;

        if ($bold) {
            if ($strong === null) {
                $strong = $this->findFirstExistingPath($this->fontCandidates(true));
            }

            return $strong;
        }

        if ($regular === null) {
            $regular = $this->findFirstExistingPath($this->fontCandidates());
        }

        return $regular;
    }

    /**
     * @return array<int, string>
     */
    protected function fontCandidates(bool $bold = false): array
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

    protected function siteInitials(): string
    {
        return Str::of($this->siteSettings->values()['site_name'])
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('')
            ->value();
    }

    protected function resolveSiteLogoImage(): ?GdImage
    {
        $settings = $this->siteSettings->values();

        foreach ([
            $settings['site_logo_path'],
            $settings['og_image_path'],
            $settings['favicon_path'],
        ] as $path) {
            if (! filled($path) || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $image = $this->imageFromFile(Storage::disk('public')->path($path));

            if ($image instanceof GdImage) {
                return $image;
            }
        }

        foreach ([
            public_path('images/ruangbaca.png'),
            public_path('favicon-32x32.png'),
            public_path('apple-touch-icon.png'),
        ] as $path) {
            if (! is_file($path)) {
                continue;
            }

            $image = $this->imageFromFile($path);

            if ($image instanceof GdImage) {
                return $image;
            }
        }

        return null;
    }

    protected function imageFromFile(string $absolutePath): ?GdImage
    {
        $contents = @file_get_contents($absolutePath);

        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        return $image instanceof GdImage ? $image : null;
    }

    /**
     * @return array<int, string>
     */
    protected function wrapText(string $text, int $maxCharactersPerLine, int $maxLines): array
    {
        $normalized = Str::of($text)->squish()->value();

        if ($normalized === '') {
            return ['-'];
        }

        $words = preg_split('/\s+/u', $normalized) ?: [];
        $lines = [];
        $currentLine = '';
        $consumedWords = 0;

        foreach ($words as $word) {
            $candidate = $currentLine === '' ? $word : "{$currentLine} {$word}";

            if (Str::length($candidate) <= $maxCharactersPerLine) {
                $currentLine = $candidate;
                $consumedWords++;

                continue;
            }

            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }

            $currentLine = $word;
            $consumedWords++;

            if (count($lines) === $maxLines - 1) {
                break;
            }
        }

        if ($currentLine !== '' && count($lines) < $maxLines) {
            $lines[] = $currentLine;
        }

        $remainingWords = array_slice($words, $consumedWords);

        if ($remainingWords !== [] && $lines !== []) {
            $lastLineIndex = array_key_last($lines);
            $lines[$lastLineIndex] = Str::limit($lines[$lastLineIndex].' '.implode(' ', $remainingWords), $maxCharactersPerLine, '...');
        }

        return $lines;
    }
}
