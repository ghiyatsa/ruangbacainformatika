<?php

namespace App\Support;

use App\Models\Book;
use App\Support\OpenGraph\FontResolver;
use App\Support\OpenGraph\GdCanvas;
use App\Support\OpenGraph\TextWrapper;
use GdImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OpenGraphImage
{
    public const MIME_TYPE = 'image/png';

    public const SITE_WIDTH = 1200;

    public const SITE_HEIGHT = 1200;

    public const DETAIL_WIDTH = 1200;

    public const DETAIL_HEIGHT = 600;

    public function __construct(
        protected GdCanvas $canvas,
        protected FontResolver $fonts,
        protected SiteSettings $siteSettings,
        protected TextWrapper $text,
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

        return $this->canvas->renderPng(self::SITE_WIDTH, self::SITE_HEIGHT, function (GdImage $image) use ($settings): void {
            $colors = $this->canvas->palette($image, $settings['theme_color']);
            $canvasSize = self::SITE_WIDTH;
            $cardInset = 100;
            $cardRadius = 52;
            $logoInset = 220;

            imagefilledrectangle($image, 0, 0, $canvasSize, $canvasSize, $colors['white']);

            imagefilledrectangle($image, 0, 0, $canvasSize, 24, $colors['theme']);
            imagefilledrectangle($image, 0, $canvasSize - 24, $canvasSize, $canvasSize, $colors['theme']);

            $this->canvas->drawRoundedRectangle(
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

        return $this->canvas->renderPng(self::DETAIL_WIDTH, self::DETAIL_HEIGHT, function (GdImage $image) use ($label, $title, $author, $views, $settings): void {
            $colors = $this->canvas->palette($image, $settings['theme_color']);
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
            $this->canvas->drawRoundedRectangle(
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
            $titleMaxWidth = $logoBoxLeft - $paddingX - 40;

            // Determine optimal font size starting from 40 down to 24
            $fontSize = 40;
            $titleLines = [];
            for ($size = 40; $size >= 24; $size -= 4) {
                $fontSize = $size;
                $titleLines = $this->text->wrapToPixelWidthNoTruncate($title, $titleMaxWidth, $fontSize, true);
                if (count($titleLines) <= 3) {
                    break;
                }
            }

            // Calculate height of the title block
            $lineHeight = (int) round($fontSize * 1.35);
            $titleHeight = (count($titleLines) - 1) * $lineHeight + $fontSize + (int) round($fontSize * 0.2);

            // Wrap all author names without truncation
            $authorText = 'Oleh: '.$author;
            $authorFontSize = 22;
            $authorLineHeight = (int) round($authorFontSize * 1.35);
            $authorLines = $this->text->wrapToPixelWidthNoTruncate($authorText, $titleMaxWidth, $authorFontSize, false);
            $authorHeight = (count($authorLines) - 1) * $authorLineHeight + $authorFontSize + (int) round($authorFontSize * 0.2);

            $gapBetweenTitleAndAuthor = 18;
            $totalContentHeight = $titleHeight + $gapBetweenTitleAndAuthor + $authorHeight;

            // Center the content vertically in the space below the fixed badge (Y=118 to Y=490, which is 372px height)
            $contentStartY = 118 + (int) floor((372 - $totalContentHeight) / 2);
            $contentStartY = max(130, $contentStartY); // Ensure at least a small gap below the badge

            // Draw label pill: left-aligned at $paddingX, fixed at Y=80 (sejajar dengan logo)
            $badgeY = 80;
            $labelPillH = 38;
            $labelFontPath = $this->fonts->path(false);
            $labelPillW = 220; // default fallback
            if ($labelFontPath !== null) {
                $box = imagettfbbox(16, 0, $labelFontPath, $label);
                if (is_array($box)) {
                    $labelPillW = (int) abs($box[4] - $box[0]) + 48;
                }
            }
            $this->canvas->drawRoundedRectangle(
                $image,
                $paddingX,
                $badgeY,
                $paddingX + $labelPillW,
                $badgeY + $labelPillH,
                10,
                $colors['slate200']
            );
            $this->canvas->drawCenteredTextLine(
                $image,
                $label,
                $paddingX + (int) floor($labelPillW / 2),
                $badgeY + 26,
                16,
                $colors['slate700'],
                false
            );

            // Title: left-aligned at $paddingX and vertically centered
            $titleY = $contentStartY + $fontSize;
            foreach ($titleLines as $index => $line) {
                $this->canvas->drawTextLine(
                    $image,
                    $line,
                    $paddingX,
                    $titleY + ($index * $lineHeight),
                    $fontSize,
                    $colors['slate900'],
                    true
                );
            }

            // Author: drawn directly below the title, left-aligned
            $authorY = $contentStartY + $titleHeight + $gapBetweenTitleAndAuthor + $authorFontSize;
            foreach ($authorLines as $index => $line) {
                $this->canvas->drawTextLine(
                    $image,
                    $line,
                    $paddingX,
                    $authorY + ($index * $authorLineHeight),
                    $authorFontSize,
                    $colors['slate600'],
                    false
                );
            }

            // Separator line above bottom row
            $separatorY = $height - $stripeHeight - 90;
            imageline($image, $paddingX, $separatorY, $width - $paddingX, $separatorY, $colors['slate200']);

            // Bottom row stats (GitHub-style)
            $bottomTextY = $separatorY + 56;
            $cursor = $paddingX;
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
            $this->canvas->drawTextLine($image, $viewsLabel, $cursor, $bottomTextY, 26, $colors['slate600']);

            // Site name right-aligned
            $siteName = $settings['site_name'];
            $boldFontPath = $this->fonts->path(true);
            $siteX = $width - $paddingX;
            if ($boldFontPath !== null) {
                $box = imagettfbbox(22, 0, $boldFontPath, $siteName);
                if (is_array($box)) {
                    $siteTextW = (int) abs($box[4] - $box[0]);
                    $siteX = $width - $paddingX - $siteTextW;
                }
            }
            $this->canvas->drawTextLine($image, $siteName, $siteX, $bottomTextY, 22, $colors['slate400'], true);
        });
    }

    /**
     * @param  array<string, int>  $colors
     */
    protected function drawLogoCard(GdImage $image, int $x, int $y, int $width, int $height, array $colors): void
    {
        $this->canvas->drawRoundedRectangle($image, $x, $y, $x + $width, $y + $height, 32, $colors['white']);
        $this->drawLogo($image, $x, $y, $width, $height, $colors);
    }

    /**
     * @param  array<string, int>  $colors
     */
    protected function drawLogo(GdImage $image, int $x, int $y, int $width, int $height, array $colors): void
    {
        $logo = $this->resolveSiteLogoImage();

        if ($logo instanceof GdImage) {
            $this->canvas->copyCenteredImage($image, $logo, $x, $y, $width, $height);
            imagedestroy($logo);

            return;
        }

        $this->canvas->drawRoundedRectangle($image, $x, $y, $x + $width, $y + $height, 32, $colors['slate200']);
        $this->canvas->drawCenteredTextLine($image, $this->siteInitials(), $x + (int) floor($width / 2), $y + (int) floor($height * 0.61), 52, $colors['slate900'], true);
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
                $settings['apple_touch_icon_path'],
                $settings['favicon_path'],
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
                public_path('images/ruangbaca.png'),
                public_path('android-chrome-512x512.png'),
                public_path('android-chrome-192x192.png'),
                public_path('apple-touch-icon.png'),
                public_path('favicon-32x32.png'),
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
}
