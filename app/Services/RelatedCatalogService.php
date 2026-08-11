<?php

namespace App\Services;

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RelatedCatalogService
{
    private const RESULT_LIMIT = 4;

    /**
     * @return Collection<int, Book>
     */
    public function forBook(Book $book, int $limit = self::RESULT_LIMIT): Collection
    {
        $book->loadMissing(['authors:id', 'categories:id']);

        $categoryIds = $book->categories->modelKeys();
        $authorIds = $book->authors->modelKeys();
        $titleTerms = $this->extractTerms($book->title);

        $query = Book::query()
            ->published()
            ->whereKeyNot($book->getKey());

        $this->applyBookMatches($query, $book, $categoryIds, $authorIds, $titleTerms);

        $related = $this->bookResults($query, $limit);

        return $related->isNotEmpty()
            ? $related
            : $this->bookResults(Book::query()->published()->whereKeyNot($book->getKey()), $limit);
    }

    /**
     * @return Collection<int, Skripsi>
     */
    public function forSkripsi(Skripsi $skripsi, int $limit = self::RESULT_LIMIT): Collection
    {
        $query = Skripsi::query()->whereKeyNot($skripsi->getKey());

        $this->applyAcademicMatches($query, $skripsi);

        $related = $this->skripsiResults($query, $limit);

        return $related->isNotEmpty()
            ? $related
            : $this->skripsiResults(Skripsi::query()->whereKeyNot($skripsi->getKey()), $limit);
    }

    /**
     * @return Collection<int, Thesis>
     */
    public function forThesis(Thesis $thesis, int $limit = self::RESULT_LIMIT): Collection
    {
        $query = Thesis::query()->whereKeyNot($thesis->getKey());

        $this->applyAcademicMatches($query, $thesis);

        $related = $this->thesisResults($query, $limit);

        return $related->isNotEmpty()
            ? $related
            : $this->thesisResults(Thesis::query()->whereKeyNot($thesis->getKey()), $limit);
    }

    /**
     * @return Collection<int, InternshipReport>
     */
    public function forInternshipReport(InternshipReport $report, int $limit = self::RESULT_LIMIT): Collection
    {
        $query = InternshipReport::query()->whereKeyNot($report->getKey());

        $this->applyAcademicMatches($query, $report);

        $related = $this->internshipReportResults($query, $limit);

        return $related->isNotEmpty()
            ? $related
            : $this->internshipReportResults(
                InternshipReport::query()->whereKeyNot($report->getKey()),
                $limit,
            );
    }

    /**
     * @param  array<int, int|string>  $categoryIds
     * @param  array<int, int|string>  $authorIds
     * @param  list<string>  $titleTerms
     */
    private function applyBookMatches(
        Builder $query,
        Book $book,
        array $categoryIds,
        array $authorIds,
        array $titleTerms,
    ): void {
        $query->where(function (Builder $matches) use ($book, $categoryIds, $authorIds, $titleTerms): void {
            $matches
                ->when($categoryIds !== [], fn (Builder $query) => $query
                    ->orWhereHas('categories', fn (Builder $categories) => $categories->whereKey($categoryIds)))
                ->when($authorIds !== [], fn (Builder $query) => $query
                    ->orWhereHas('authors', fn (Builder $authors) => $authors->whereKey($authorIds)))
                ->when($book->publisher_id !== null, fn (Builder $query) => $query
                    ->orWhere('publisher_id', $book->publisher_id))
                ->when($book->published_year !== null, fn (Builder $query) => $query
                    ->orWhereBetween('published_year', [$book->published_year - 1, $book->published_year + 1]));

            foreach ($titleTerms as $term) {
                $matches->orWhere('title', 'like', "%{$term}%");
            }
        });
    }

    /**
     * @param  Builder<Book>  $query
     * @return Collection<int, Book>
     */
    private function bookResults(Builder $query, int $limit): Collection
    {
        return $query
            ->with(['authors:id,name', 'categories:id,name,slug', 'publisher:id,name'])
            ->withCount([
                'items',
                'items as available_items_count' => fn (Builder $query) => $query->available(),
            ])
            ->orderByDesc('view_count')
            ->orderByDesc('published_year')
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Builder<Skripsi>  $query
     * @return Collection<int, Skripsi>
     */
    private function skripsiResults(Builder $query, int $limit): Collection
    {
        return $query
            ->orderByDesc('view_count')
            ->orderByDesc('year')
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Builder<Thesis>  $query
     * @return Collection<int, Thesis>
     */
    private function thesisResults(Builder $query, int $limit): Collection
    {
        return $query
            ->orderByDesc('view_count')
            ->orderByDesc('year')
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Builder<InternshipReport>  $query
     * @return Collection<int, InternshipReport>
     */
    private function internshipReportResults(Builder $query, int $limit): Collection
    {
        return $query
            ->orderByDesc('view_count')
            ->orderByDesc('year')
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    private function applyAcademicMatches(Builder $query, Model $document): void
    {
        $terms = array_slice([
            ...$this->extractTerms((string) $document->getAttribute('keywords')),
            ...$this->extractTerms((string) $document->getAttribute('title')),
        ], 0, 6);
        $authorName = (string) $document->getAttribute('author_name');
        $year = $document->getAttribute('year');

        $query->where(function (Builder $matches) use ($terms, $authorName, $year): void {
            $matches
                ->when($year !== null, fn (Builder $query) => $query
                    ->orWhereBetween('year', [(int) $year - 1, (int) $year + 1]))
                ->when($authorName !== '', fn (Builder $query) => $query
                    ->orWhere('author_name', $authorName));

            foreach ($terms as $term) {
                $matches
                    ->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('keywords', 'like', "%{$term}%")
                    ->orWhere('abstract', 'like', "%{$term}%");
            }
        });
    }

    /**
     * @return list<string>
     */
    private function extractTerms(string $value): array
    {
        return Str::of(Str::lower($value))
            ->replaceMatches('/[^\pL\pN\s]+/u', ' ')
            ->explode(' ')
            ->map(fn (string $term): string => trim($term))
            ->filter(fn (string $term): bool => mb_strlen($term) >= 4)
            ->unique()
            ->values()
            ->all();
    }
}
