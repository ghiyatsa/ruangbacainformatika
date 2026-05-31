<?php

namespace App\Support;

use App\Models\Book;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PageMeta
{
    public function __construct(
        protected SiteSettings $siteSettings,
        protected OpenGraphImage $openGraphImage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forWelcome(): array
    {
        return [
            'title' => $this->siteTitle(),
            'description' => 'Daftar buku dan arsip akademik Ruang Baca Teknik Informatika Universitas Malikussaleh.',
            'keywords' => $this->siteKeywords(),
            'robots' => $this->siteRobots(),
            'canonicalUrl' => url('/'),
            'type' => 'website',
            ...$this->openGraphImage->defaultMeta(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forBook(Book $book): array
    {
        $keywords = collect([
            $book->title,
            ...$book->authors->pluck('name')->all(),
            ...$book->categories->pluck('name')->all(),
            'katalog buku',
            'ruang baca informatika',
        ])
            ->filter()
            ->implode(', ');

        return [
            'title' => $this->fullTitle($book->title),
            'description' => $this->excerpt(
                $book->description ?: "{$book->title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.",
            ),
            'keywords' => $keywords,
            'robots' => $this->siteRobots(),
            'canonicalUrl' => route('books.show', $book),
            'type' => 'article',
            ...$this->openGraphImage->bookMeta($book),
        ];
    }

    /**
     * @param  array<int, string>  $keywords
     * @return array<string, mixed>
     */
    public function forAcademicDocument(
        string $title,
        string $authorName,
        string $studentId,
        ?string $abstract,
        array $keywords,
        string $catalogLabel,
        string $canonicalUrl,
        string $ogRouteName,
        Model $document,
    ): array {
        $metaKeywords = collect([
            $title,
            $authorName,
            $studentId,
            ...$keywords,
            "{$catalogLabel} informatika",
            'ruang baca informatika',
        ])
            ->filter()
            ->implode(', ');

        return [
            'title' => $this->fullTitle($title),
            'description' => $this->excerpt(
                $abstract ?: "{$title} tersedia di Ruang Baca Teknik Informatika Universitas Malikussaleh.",
            ),
            'keywords' => $metaKeywords,
            'robots' => $this->siteRobots(),
            'canonicalUrl' => $canonicalUrl,
            'type' => 'article',
            ...$this->openGraphImage->academicDocumentMeta($ogRouteName, $document),
        ];
    }

    protected function fullTitle(string $pageTitle): string
    {
        return "{$pageTitle} - {$this->siteTitle()}";
    }

    protected function siteTitle(): string
    {
        return strval($this->siteMeta()['title'] ?? config('app.name'));
    }

    protected function siteKeywords(): ?string
    {
        $keywords = $this->siteMeta()['keywords'] ?? null;

        return filled($keywords) ? strval($keywords) : null;
    }

    protected function siteRobots(): string
    {
        return strval($this->siteMeta()['robots'] ?? 'index,follow');
    }

    protected function excerpt(string $content): string
    {
        return Str::of($content)
            ->squish()
            ->substr(0, 160)
            ->value();
    }

    /**
     * @return array<string, mixed>
     */
    protected function siteMeta(): array
    {
        return $this->siteSettings->rootViewData()['siteMeta'] ?? [];
    }
}
