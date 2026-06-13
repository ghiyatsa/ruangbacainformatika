<?php

namespace App\Services\Borrowing;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class LoanQrCodeService
{
    public function generateSvg(string $payload): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(
                    192,
                    0,
                    new RoundnessModule(0.8),
                    SimpleCircleEye::instance(),
                    Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(17, 24, 39))
                ),
                new SvgImageBackEnd
            )
        ))->writeString($payload);

        $svg = trim(substr($svg, strpos($svg, "\n") + 1));
        $svg = (string) preg_replace('/<rect\b([^>]*)fill="#ffffff"([^>]*)><\/rect>/i', '<rect$1fill="transparent"$2></rect>', $svg);
        $svg = (string) preg_replace('/<path\b([^>]*)fill="#111827"([^>]*)>/i', '<path$1fill="currentColor"$2>', $svg);
        $svg = (string) preg_replace('/fill="#ffffff"/i', 'fill="transparent"', $svg);
        $svg = (string) preg_replace('/fill="#111827"/i', 'fill="currentColor"', $svg);

        return str_replace('<svg ', '<svg color="currentColor" ', $svg);
    }
}
