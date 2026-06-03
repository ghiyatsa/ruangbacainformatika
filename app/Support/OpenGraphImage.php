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

    public const SITE_WIDTH = 1200;

    public const SITE_HEIGHT = 1200;

    public const DETAIL_WIDTH = 1200;

    public const DETAIL_HEIGHT = 600;

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
            'ogImageWidth' => self::SITE_WIDTH,
            'ogImageHeight' => self::SITE_HEIGHT,
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
            'ogImageWidth' => self::DETAIL_WIDTH,
            'ogImageHeight' => self::DETAIL_HEIGHT,
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
            'ogImageWidth' => self::DETAIL_WIDTH,
            'ogImageHeight' => self::DETAIL_HEIGHT,
        ];
    }

    public function renderSite(): string
    {
        $settings = $this->siteSettings->values();

        return $this->renderPng(self::SITE_WIDTH, self::SITE_HEIGHT, function (GdImage $image) use ($settings): void {
            $colors = $this->palette($image, $settings['theme_color']);
            $canvasSize = self::SITE_WIDTH;
            $cardInset = 100;
            $cardRadius = 52;
            $logoInset = 220;

            imagefilledrectangle($image, 0, 0, $canvasSize, $canvasSize, $colors['white']);

            imagefilledrectangle($image, 0, 0, $canvasSize, 24, $colors['theme']);
            imagefilledrectangle($image, 0, $canvasSize - 24, $canvasSize, $canvasSize, $colors['theme']);

            $this->drawRoundedRectangle(
                $image,
                $cardInset,
                $cardInset,
                $canvasSize - $cardInset,
                $canvasSize - $cardInset,
                $cardRadius,
                $colors['slate50'],
                $colors['slate200']
            );

            $this->drawLogoCard(
                $image,
                $logoInset,
                $logoInset,
                $canvasSize - ($logoInset * 2),
                $canvasSize - ($logoInset * 2),
                $colors
            );
        });
    }

    public function renderCatalogDetail(
        string $label,
        string $title,
        string $author,
        int $views = 0,
    ): string {
        $settings = $this->siteSettings->values();

        return $this->renderPng(self::DETAIL_WIDTH, self::DETAIL_HEIGHT, function (GdImage $image) use ($label, $title, $author, $views, $settings): void {
            $colors = $this->palette($image, $settings['theme_color']);
            $width = self::DETAIL_WIDTH;
            $height = self::DETAIL_HEIGHT;

            // White background
            imagefilledrectangle($image, 0, 0, $width, $height, $colors['white']);

            // Bottom colorful stripe (mimics GitHub's multi-color footer bar)
            $stripeHeight = 20;
            $stripeY = $height - $stripeHeight;
            $thirdWidth = (int) floor($width / 3);
            imagefilledrectangle($image, 0, $stripeY, $thirdWidth - 4, $height, $colors['theme']);
            imagefilledrectangle($image, $thirdWidth, $stripeY, $thirdWidth * 2 - 4, $height, $colors['slate600']);
            imagefilledrectangle($image, $thirdWidth * 2, $stripeY, $width, $height, $colors['theme']);

            // Content margins
            $paddingX = 80;
            $paddingY = 80;
            $logoBoxSize = 180;
            $logoBoxLeft = $width - $paddingX - $logoBoxSize;
            $logoBoxTop = $paddingY;

            // Logo area: rounded square top-right (like GitHub avatar)
            $this->drawRoundedRectangle(
                $image,
                $logoBoxLeft,
                $logoBoxTop,
                $logoBoxLeft + $logoBoxSize,
                $logoBoxTop + $logoBoxSize,
                24,
                $colors['slate50'],
                $colors['slate200']
            );
            $this->drawLogo($image, $logoBoxLeft, $logoBoxTop, $logoBoxSize, $logoBoxSize, $colors);

            // Title: large bold, constrained to area left of logo
            // Gap of 40px between title right edge and logo left edge
            $titleMaxWidth = $logoBoxLeft - $paddingX - 40;
            $textAreaCenterX = $paddingX + (int) floor($titleMaxWidth / 2);

            // Label pill top-left (now centered within text area)
            $labelFontPath = $this->fontPath(false);
            $labelPillW = 220; // default fallback
            if ($labelFontPath !== null) {
                $box = imagettfbbox(16, 0, $labelFontPath, $label);
                if (is_array($box)) {
                    $labelPillW = (int) abs($box[4] - $box[0]) + 48;
                }
            }
            $labelPillH = 38;
            $labelPillLeft = $textAreaCenterX - (int) floor($labelPillW / 2);
            $this->drawRoundedRectangle(
                $image,
                $labelPillLeft,
                $paddingY,
                $labelPillLeft + $labelPillW,
                $paddingY + $labelPillH,
                10,
                $colors['slate200']
            );
            $this->drawCenteredTextLine(
                $image,
                $label,
                $textAreaCenterX,
                $paddingY + 26,
                16,
                $colors['slate700'],
                false
            );

            // Determine optimal font size starting from 40 down to 24
            $fontSize = 40;
            $titleLines = [];
            for ($size = 40; $size >= 24; $size -= 4) {
                $fontSize = $size;
                $titleLines = $this->wrapTextToPixelWidthNoTruncate($title, $titleMaxWidth, $fontSize, true);
                if (count($titleLines) <= 3) {
                    break;
                }
            }

            $titleY = $paddingY + $labelPillH + 64;
            $lineHeight = (int) round($fontSize * 1.35);
            foreach ($titleLines as $index => $line) {
                $this->drawCenteredTextLine(
                    $image,
                    $line,
                    $textAreaCenterX,
                    $titleY + ($index * $lineHeight),
                    $fontSize,
                    $colors['slate900'],
                    true
                );
            }

            // Separator line above bottom row
            $separatorY = $height - $stripeHeight - 90;
            imageline($image, $paddingX, $separatorY, $width - $paddingX, $separatorY, $colors['slate200']);

            // Bottom row stats (GitHub-style)
            $bottomTextY = $separatorY + 56;
            $cursor = $paddingX;
            $statGap = 52;
            $iconR = 11;

            // --- Eye icon (views) ---
            $eyeCX = $cursor + $iconR;
            $eyeCY = $bottomTextY - $iconR;
            // outer ellipse (eye shape)
            imageellipse($image, $eyeCX, $eyeCY, $iconR * 2, (int) round($iconR * 1.2), $colors['slate400']);
            // pupil
            imagefilledellipse($image, $eyeCX, $eyeCY, (int) round($iconR * 0.72), (int) round($iconR * 0.72), $colors['slate400']);
            $cursor += $iconR * 2 + 10;

            // views count
            $viewsLabel = number_format($views).' dilihat';
            $this->drawTextLine($image, $viewsLabel, $cursor, $bottomTextY, 26, $colors['slate600']);

            // measure views text width to position next stat
            $fontPath = $this->fontPath(false);
            if ($fontPath !== null) {
                $box = imagettfbbox(26, 0, $fontPath, $viewsLabel);
                if (is_array($box)) {
                    $cursor += (int) abs($box[4] - $box[0]) + $statGap;
                }
            } else {
                $cursor += 200;
            }

            // --- Person icon (author) ---
            $personCX = $cursor + $iconR;
            $personCY = $bottomTextY - $iconR - 2;
            // head
            imagefilledellipse($image, $personCX, $personCY, $iconR, $iconR, $colors['slate400']);
            // body arc
            imagearc($image, $personCX, $personCY + $iconR, (int) round($iconR * 1.6), $iconR, 180, 360, $colors['slate400']);
            $cursor += $iconR * 2 + 10;

            // Parse author list: only show 1 author and a count of others if multiple
            $authorsList = collect(explode(',', $author))
                ->map(fn ($name) => trim($name))
                ->filter()
                ->values();

            if ($authorsList->count() > 1) {
                $authorDisplay = $authorsList->first().' + '.($authorsList->count() - 1);
            } else {
                $authorDisplay = $authorsList->first() ?: 'Ruang Baca Informatika';
            }

            // author name (truncated)
            $this->drawTextLine($image, Str::limit($authorDisplay, 50), $cursor, $bottomTextY, 26, $colors['slate600']);

            // Site name right-aligned
            $siteName = $settings['site_name'];
            $boldFontPath = $this->fontPath(true);
            $siteX = $width - $paddingX;
            if ($boldFontPath !== null) {
                $box = imagettfbbox(22, 0, $boldFontPath, $siteName);
                if (is_array($box)) {
                    $siteTextW = (int) abs($box[4] - $box[0]);
                    $siteX = $width - $paddingX - $siteTextW;
                }
            }
            $this->drawTextLine($image, $siteName, $siteX, $bottomTextY, 22, $colors['slate400'], true);
        });
    }

    protected function renderPng(int $width, int $height, callable $render): string
    {
        $image = imagecreatetruecolor($width, $height);
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
            ->implode('');
    }

    protected function resolveSiteLogoImage(): ?GdImage
    {
        $settings = $this->siteSettings->values();

        foreach (
            [
                $settings['site_logo_path'],
                $settings['favicon_path'],
                $settings['apple_touch_icon_path'],
            ] as $path
        ) {
            if (! filled($path) || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $image = $this->imageFromFile(Storage::disk('public')->path($path));

            if ($image instanceof GdImage) {
                return $image;
            }
        }

        foreach (
            [
                public_path('images/ruangbaca.svg'),
                public_path('favicon-32x32.png'),
                public_path('apple-touch-icon.png'),
            ] as $path
        ) {
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

    /**
     * Wrap text to fit within a maximum pixel width using TTF font metrics.
     * Falls back to character-based wrapping when no font is available.
     *
     * @return array<int, string>
     */
    protected function wrapTextToPixelWidth(
        string $text,
        int $maxPixelWidth,
        int $maxLines,
        int $fontSize,
        bool $bold = false,
    ): array {
        $fontPath = $this->fontPath($bold);

        if ($fontPath === null) {
            // Fallback: rough estimate of ~14px per char at size 52
            $charsPerLine = max(10, (int) floor($maxPixelWidth / 14));

            return $this->wrapText($text, $charsPerLine, $maxLines);
        }

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
            $box = imagettfbbox($fontSize, 0, $fontPath, $candidate);
            $candidateWidth = is_array($box) ? (int) abs($box[4] - $box[0]) : PHP_INT_MAX;

            if ($candidateWidth <= $maxPixelWidth) {
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

        // Append any remaining words as truncated ellipsis on the last line
        $remainingWords = array_slice($words, $consumedWords);

        if ($remainingWords !== [] && $lines !== []) {
            $lastIndex = array_key_last($lines);
            $full = $lines[$lastIndex].' '.implode(' ', $remainingWords);

            // Truncate character by character until it fits with '…'
            while (Str::length($full) > 1) {
                $truncated = Str::of($full)->limit(Str::length($full) - 1, '…')->value();
                $box = imagettfbbox($fontSize, 0, $fontPath, $truncated);
                $w = is_array($box) ? (int) abs($box[4] - $box[0]) : PHP_INT_MAX;

                if ($w <= $maxPixelWidth) {
                    $lines[$lastIndex] = $truncated;
                    break;
                }

                $full = Str::substr($full, 0, Str::length($full) - 1);
            }
        }

        return $lines ?: ['-'];
    }

    /**
     * Wrap text to fit within a maximum pixel width without truncating.
     *
     * @return array<int, string>
     */
    protected function wrapTextToPixelWidthNoTruncate(
        string $text,
        int $maxPixelWidth,
        int $fontSize,
        bool $bold = false,
    ): array {
        $fontPath = $this->fontPath($bold);

        if ($fontPath === null) {
            $charsPerLine = max(10, (int) floor($maxPixelWidth / 10));

            return $this->wrapTextNoTruncate($text, $charsPerLine);
        }

        $normalized = Str::of($text)->squish()->value();

        if ($normalized === '') {
            return ['-'];
        }

        $words = preg_split('/\s+/u', $normalized) ?: [];
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $candidate = $currentLine === '' ? $word : "{$currentLine} {$word}";
            $box = imagettfbbox($fontSize, 0, $fontPath, $candidate);
            $candidateWidth = is_array($box) ? (int) abs($box[4] - $box[0]) : PHP_INT_MAX;

            if ($candidateWidth <= $maxPixelWidth) {
                $currentLine = $candidate;
            } else {
                if ($currentLine !== '') {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    /**
     * Wrap text by character length fallback without truncating.
     *
     * @return array<int, string>
     */
    protected function wrapTextNoTruncate(string $text, int $charsPerLine): array
    {
        $wrapped = wordwrap($text, $charsPerLine, "\n", true);

        return explode("\n", $wrapped);
    }
}
