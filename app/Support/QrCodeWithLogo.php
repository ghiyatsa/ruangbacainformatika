<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

class QrCodeWithLogo
{
    protected const DEFAULT_SIZE = 240;

    protected const LOGO_RATIO = 0.24; // Logo takes ~24% of the QR width (safe for EC Level H)

    /**
     * Generate an SVG QR code string with an embedded logo in the center.
     */
    public static function generateSvg(
        string $payload,
        int $size = self::DEFAULT_SIZE,
        bool $withLogo = true,
        string $fillColor = 'currentColor',
        string $bgColor = 'transparent'
    ): string {
        try {
            $writer = new Writer(
                new ImageRenderer(
                    new RendererStyle(
                        $size,
                        0,
                        new RoundnessModule(0.8),
                        SimpleCircleEye::instance(),
                        Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(17, 24, 39))
                    ),
                    new SvgImageBackEnd
                )
            );

            // Error Correction H is mandatory when covering the center with a logo
            $svg = $writer->writeString($payload, 'UTF-8', ErrorCorrectionLevel::H());
        } catch (Throwable $exception) {
            throw new RuntimeException('QR code generation failed.', previous: $exception);
        }

        $svg = trim(substr($svg, strpos($svg, "\n") + 1));

        // Replace raw BaconQrCode colors
        $svg = (string) preg_replace('/<rect\b([^>]*)fill="#ffffff"([^>]*)><\/rect>/i', '<rect$1fill="'.$bgColor.'"$2></rect>', $svg);
        $svg = (string) preg_replace('/<path\b([^>]*)fill="#111827"([^>]*)>/i', '<path$1fill="'.$fillColor.'"$2>', $svg);
        $svg = (string) preg_replace('/fill="#ffffff"/i', 'fill="'.$bgColor.'"', $svg);
        $svg = (string) preg_replace('/fill="#111827"/i', 'fill="'.$fillColor.'"', $svg);

        if ($withLogo) {
            $logoBadge = self::renderLogoBadge($size, $bgColor);
            $svg = str_replace('</svg>', $logoBadge.'</svg>', $svg);
        }

        return str_replace('<svg ', '<svg color="'.$fillColor.'" ', $svg);
    }

    protected static function renderLogoBadge(int $size, string $bgColor): string
    {
        $logoSvgPath = public_path('images/ruangbaca.svg');
        if (! File::exists($logoSvgPath)) {
            return '';
        }

        $logoContent = File::get($logoSvgPath);

        // Extract viewBox from source logo
        preg_match('/viewBox="([^"]+)"/', $logoContent, $matches);
        $viewBox = $matches[1] ?? '0 0 3400 3400';

        // Extract inner SVG content
        $innerSvg = preg_replace('/<\?xml.*?\?>/s', '', $logoContent);
        $innerSvg = preg_replace('/<!DOCTYPE.*?>/s', '', $innerSvg);
        $innerSvg = preg_replace('/^<svg[^>]*>/s', '', trim($innerSvg));
        $innerSvg = preg_replace('/<\/svg>$/s', '', trim($innerSvg));

        $badgeSize = (int) round($size * self::LOGO_RATIO);
        $badgePos = (int) round(($size - $badgeSize) / 2);
        $badgeRadius = (int) round($badgeSize * 0.22);

        $innerPadding = (int) round($badgeSize * 0.12);
        $innerSize = $badgeSize - ($innerPadding * 2);
        $innerPos = $badgePos + $innerPadding;

        // Mask background under logo so QR dark modules do not bleed into the logo
        $maskFill = $bgColor === 'transparent' ? '#ffffff' : $bgColor;

        return sprintf(
            '<g class="qr-logo-badge">'
            .'<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s" stroke="none"/>'
            .'<svg x="%d" y="%d" width="%d" height="%d" viewBox="%s">%s</svg>'
            .'</g>',
            $badgePos,
            $badgePos,
            $badgeSize,
            $badgeSize,
            $badgeRadius,
            $maskFill,
            $innerPos,
            $innerPos,
            $innerSize,
            $innerSize,
            $viewBox,
            $innerSvg
        );
    }
}
