<?php

namespace App\Support\OpenGraph;

use GdImage;
use Illuminate\Support\Str;
use RuntimeException;

class GdCanvas
{
    public function __construct(
        protected FontResolver $fonts,
    ) {}

    public function renderPng(int $width, int $height, callable $render): string
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
    public function palette(GdImage $image, string $themeColor): array
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

    public function drawRoundedRectangle(
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

    public function copyCenteredImage(
        GdImage $destination,
        GdImage $source,
        int $x,
        int $y,
        int $width,
        int $height,
    ): void {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $scale = min($width / $sourceWidth, $height / $sourceHeight);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $targetX = $x + (int) floor(($width - $targetWidth) / 2);
        $targetY = $y + (int) floor(($height - $targetHeight) / 2);

        imagecopyresampled($destination, $source, $targetX, $targetY, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
    }

    public function drawTextLine(
        GdImage $image,
        string $text,
        int $x,
        int $baselineY,
        int $size,
        int $color,
        bool $bold = false,
    ): void {
        $fontPath = $this->fonts->path($bold);

        if ($fontPath !== null) {
            imagettftext($image, $size, 0, $x, $baselineY, $color, $fontPath, $text);

            return;
        }

        imagestring($image, 5, $x, max(0, $baselineY - 20), Str::limit($text, 80), $color);
    }

    public function drawCenteredTextLine(
        GdImage $image,
        string $text,
        int $centerX,
        int $baselineY,
        int $size,
        int $color,
        bool $bold = false,
    ): void {
        $fontPath = $this->fonts->path($bold);

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
}
