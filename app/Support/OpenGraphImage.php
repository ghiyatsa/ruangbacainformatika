<?php

namespace App\Support;

use App\Models\Book;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class OpenGraphImage
{
    public const MIME_TYPE = 'image/svg+xml';

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

        return view('og.site', [
            'title' => $settings['site_name'],
            'subtitle' => $settings['site_tagline'],
            'themeColor' => $settings['theme_color'],
            'logoMarkup' => new HtmlString($this->siteLogoMarkup()),
        ])->render();
    }

    public function renderCatalogDetail(
        string $label,
        string $title,
        string $author,
    ): string {
        return view('og.catalog-detail', [
            'label' => $label,
            'titleLines' => $this->wrapText($title, 28, 3),
            'authorLines' => $this->wrapText($author, 38, 2),
            'themeColor' => $this->siteSettings->values()['theme_color'],
            'siteName' => $this->siteSettings->values()['site_name'],
            'logoMarkup' => new HtmlString($this->siteLogoMarkup(200, 200)),
        ])->render();
    }

    protected function siteLogoMarkup(int $width = 152, int $height = 152): string
    {
        $logoDataUri = $this->resolveSiteLogoDataUri();

        if ($logoDataUri !== null) {
            return <<<SVG
<image href="{$logoDataUri}" x="0" y="0" width="{$width}" height="{$height}" preserveAspectRatio="xMidYMid meet" />
SVG;
        }

        $initials = e(Str::of($this->siteSettings->values()['site_name'])
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode(''));
        $widthHalf = $width / 2;
        $heightText = (int) floor($height * 0.61);

        return <<<SVG
<rect x="0" y="0" width="{$width}" height="{$height}" rx="32" fill="#E2E8F0" />
<text x="{$widthHalf}" y="{$heightText}" text-anchor="middle" font-size="52" font-weight="700" fill="#0F172A">{$initials}</text>
SVG;
    }

    protected function resolveSiteLogoDataUri(): ?string
    {
        $settings = $this->siteSettings->values();

        foreach ([
            $settings['site_logo_path'],
            $settings['og_image_path'],
            $settings['favicon_svg_path'],
            $settings['favicon_path'],
        ] as $path) {
            if (! filled($path)) {
                continue;
            }

            $dataUri = $this->publicDiskDataUri($path);

            if ($dataUri !== null) {
                return $dataUri;
            }
        }

        return $this->publicAssetDataUri('images/ruangbaca.svg')
            ?? $this->publicAssetDataUri('favicon.svg')
            ?? $this->publicAssetDataUri('favicon-32x32.png');
    }

    protected function publicDiskDataUri(string $path): ?string
    {
        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($path);

        return $this->fileToDataUri($absolutePath);
    }

    protected function publicAssetDataUri(string $relativePath): ?string
    {
        $absolutePath = public_path($relativePath);

        if (! is_file($absolutePath)) {
            return null;
        }

        return $this->fileToDataUri($absolutePath);
    }

    protected function fileToDataUri(string $absolutePath): ?string
    {
        $contents = @file_get_contents($absolutePath);

        if ($contents === false) {
            return null;
        }

        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
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
