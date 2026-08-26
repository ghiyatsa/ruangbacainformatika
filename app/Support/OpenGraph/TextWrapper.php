<?php

namespace App\Support\OpenGraph;

use Illuminate\Support\Str;

class TextWrapper
{
    public function __construct(
        protected FontResolver $fonts,
    ) {}

    /**
     * @return array<int, string>
     */
    public function wrap(string $text, int $maxCharactersPerLine, int $maxLines): array
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
    public function wrapToPixelWidth(
        string $text,
        int $maxPixelWidth,
        int $maxLines,
        int $fontSize,
        bool $bold = false,
    ): array {
        $fontPath = $this->fonts->path($bold);

        if ($fontPath === null) {
            // Fallback: rough estimate of ~14px per char at size 52
            $charsPerLine = max(10, (int) floor($maxPixelWidth / 14));

            return $this->wrap($text, $charsPerLine, $maxLines);
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
    public function wrapToPixelWidthNoTruncate(
        string $text,
        int $maxPixelWidth,
        int $fontSize,
        bool $bold = false,
    ): array {
        $fontPath = $this->fonts->path($bold);

        if ($fontPath === null) {
            $charsPerLine = max(10, (int) floor($maxPixelWidth / 10));

            return $this->wrapNoTruncate($text, $charsPerLine);
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
    public function wrapNoTruncate(string $text, int $charsPerLine): array
    {
        $wrapped = wordwrap($text, $charsPerLine, "\n", true);

        return explode("\n", $wrapped);
    }
}
