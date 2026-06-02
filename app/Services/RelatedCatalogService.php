<?php

namespace App\Services;

use App\Models\Book;
use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RelatedCatalogService
{
    private const RESULT_LIMIT = 4;

    /**
     * @return EloquentCollection<int, Book>
     */
    public function forBook(Book $book, int $limit = self::RESULT_LIMIT): EloquentCollection
    {
        $book->loadMissing([
            'authors:id,name',
            'categories:id,name,slug',
            'publisher:id,name',
        ]);

        $categoryIds = $book->categories->pluck('id')->all();
        $authorIds = $book->authors->pluck('id')->all();
        $titleTerms = $this->extractTerms($book->title);

        $candidates = Book::query()
            ->published()
            ->whereKeyNot($book->getKey())
            ->when(
                $categoryIds !== [] || $authorIds !== [] || $book->publisher_id !== null || $book->published_year !== null || $titleTerms !== [],
                function (Builder $query) use ($categoryIds, $authorIds, $book, $titleTerms): void {
                    $query->where(function (Builder $inner) use ($categoryIds, $authorIds, $book, $titleTerms): void {
                        if ($categoryIds !== []) {
                            $inner->orWhereHas(
                                'categories',
                                fn (Builder $categories): Builder => $categories->whereIn('categories.id', $categoryIds),
                            );
                        }

                        if ($authorIds !== []) {
                            $inner->orWhereHas(
                                'authors',
                                fn (Builder $authors): Builder => $authors->whereIn('authors.id', $authorIds),
                            );
                        }

                        if ($book->publisher_id !== null) {
                            $inner->orWhere('publisher_id', $book->publisher_id);
                        }

                        if ($book->published_year !== null) {
                            $inner->orWhereBetween('published_year', [
                                $book->published_year - 1,
                                $book->published_year + 1,
                            ]);
                        }

                        foreach ($titleTerms as $term) {
                            $inner->orWhere('title', 'like', "%{$term}%");
                        }
                    });
                }
            )
            ->with([
                'authors:id,name',
                'categories:id,name,slug',
                'publisher:id,name',
            ])
            ->withCount([
                'items',
                'items as available_items_count' => fn (Builder $query) => $query->available(),
            ])
            ->limit(24)
            ->get();

        if ($candidates->isEmpty()) {
            return Book::query()
                ->published()
                ->whereKeyNot($book->getKey())
                ->with([
                    'authors:id,name',
                    'categories:id,name,slug',
                    'publisher:id,name',
                ])
                ->withCount([
                    'items',
                    'items as available_items_count' => fn (Builder $query) => $query->available(),
                ])
                ->orderByDesc('view_count')
                ->orderByDesc('published_year')
                ->limit($limit)
                ->get();
        }

        return $this->sortByScore(
            $candidates,
            fn (Book $candidate): int => $this->scoreRelatedBook($book, $candidate),
            ['view_count', 'published_year']
        )->take($limit);
    }

    /**
     * @return EloquentCollection<int, Skripsi>
     */
    public function forSkripsi(Skripsi $skripsi, int $limit = self::RESULT_LIMIT): EloquentCollection
    {
        return $this->relatedAcademicDocuments($skripsi, $limit);
    }

    /**
     * @return EloquentCollection<int, Thesis>
     */
    public function forThesis(Thesis $thesis, int $limit = self::RESULT_LIMIT): EloquentCollection
    {
        return $this->relatedAcademicDocuments($thesis, $limit);
    }

    /**
     * @return EloquentCollection<int, InternshipReport>
     */
    public function forInternshipReport(InternshipReport $report, int $limit = self::RESULT_LIMIT): EloquentCollection
    {
        return $this->relatedAcademicDocuments($report, $limit);
    }

    /**
     * @template TModel of Model
     *
     * @param  TModel  $document
     * @return EloquentCollection<int, TModel>
     */
    private function relatedAcademicDocuments(Model $document, int $limit): EloquentCollection
    {
        /** @var Builder<TModel> $query */
        $query = $document::query();

        $keywords = $this->extractTerms((string) $document->getAttribute('keywords'));
        $titleTerms = $this->extractTerms((string) $document->getAttribute('title'));
        $authorName = (string) $document->getAttribute('author_name');
        $year = $document->getAttribute('year');

        $candidates = $query
            ->whereKeyNot($document->getKey())
            ->when(
                $keywords !== [] || $titleTerms !== [] || filled($authorName) || $year !== null,
                function (Builder $builder) use ($keywords, $titleTerms, $authorName, $year): void {
                    $builder->where(function (Builder $inner) use ($keywords, $titleTerms, $authorName, $year): void {
                        if ($year !== null) {
                            $inner->orWhereBetween('year', [(int) $year - 1, (int) $year + 1]);
                        }

                        if (filled($authorName)) {
                            $inner->orWhere('author_name', $authorName);
                        }

                        foreach (array_slice([...$keywords, ...$titleTerms], 0, 6) as $term) {
                            $inner->orWhere('title', 'like', "%{$term}%")
                                ->orWhere('keywords', 'like', "%{$term}%")
                                ->orWhere('abstract', 'like', "%{$term}%");
                        }
                    });
                }
            )
            ->limit(24)
            ->get();

        if ($candidates->isEmpty()) {
            /** @var Builder<TModel> $fallbackQuery */
            $fallbackQuery = $document::query();

            return $fallbackQuery
                ->whereKeyNot($document->getKey())
                ->orderByDesc('view_count')
                ->orderByDesc('year')
                ->limit($limit)
                ->get();
        }

        return $this->sortByScore(
            $candidates,
            fn (Model $candidate): int => $this->scoreAcademicDocument($document, $candidate),
            ['view_count', 'year']
        )->take($limit);
    }

    private function scoreRelatedBook(Book $book, Book $candidate): int
    {
        $sharedCategories = $candidate->categories
            ->pluck('id')
            ->intersect($book->categories->pluck('id'))
            ->count();
        $sharedAuthors = $candidate->authors
            ->pluck('id')
            ->intersect($book->authors->pluck('id'))
            ->count();
        $sharedTitleTerms = $this->countSharedTerms($book->title, $candidate->title);

        $score = ($sharedCategories * 24) + ($sharedAuthors * 18) + ($sharedTitleTerms * 5);

        if ($book->publisher_id !== null && $book->publisher_id === $candidate->publisher_id) {
            $score += 12;
        }

        if ($book->published_year !== null && $candidate->published_year !== null) {
            $yearDiff = abs($book->published_year - $candidate->published_year);
            $score += match (true) {
                $yearDiff === 0 => 10,
                $yearDiff === 1 => 6,
                default => 0,
            };
        }

        if ($book->is_borrowable && $candidate->is_borrowable && ($candidate->available_items_count ?? 0) > 0) {
            $score += 3;
        }

        return $score;
    }

    private function scoreAcademicDocument(Model $document, Model $candidate): int
    {
        $score = $this->countSharedTerms(
            (string) $document->getAttribute('keywords'),
            (string) $candidate->getAttribute('keywords')
        ) * 16;

        $score += $this->countSharedTerms(
            (string) $document->getAttribute('title'),
            (string) $candidate->getAttribute('title')
        ) * 8;

        $score += $this->countSharedTerms(
            (string) $document->getAttribute('abstract'),
            (string) $candidate->getAttribute('abstract')
        ) * 2;

        if (
            filled($document->getAttribute('author_name')) &&
            $document->getAttribute('author_name') === $candidate->getAttribute('author_name')
        ) {
            $score += 10;
        }

        $documentYear = $document->getAttribute('year');
        $candidateYear = $candidate->getAttribute('year');

        if ($documentYear !== null && $candidateYear !== null) {
            $yearDiff = abs((int) $documentYear - (int) $candidateYear);
            $score += match (true) {
                $yearDiff === 0 => 10,
                $yearDiff === 1 => 6,
                default => 0,
            };
        }

        return $score;
    }

    /**
     * @template TModel of Model
     *
     * @param  EloquentCollection<int, TModel>  $items
     * @param  callable(TModel): int  $scorer
     * @param  list<string>  $tieBreakers
     * @return EloquentCollection<int, TModel>
     */
    private function sortByScore(EloquentCollection $items, callable $scorer, array $tieBreakers): EloquentCollection
    {
        /** @var Collection<int, TModel> $sorted */
        $sorted = $items->sort(function (Model $left, Model $right) use ($scorer, $tieBreakers): int {
            $rightScore = $scorer($right);
            $leftScore = $scorer($left);

            if ($rightScore !== $leftScore) {
                return $rightScore <=> $leftScore;
            }

            foreach ($tieBreakers as $column) {
                $rightValue = (int) ($right->getAttribute($column) ?? 0);
                $leftValue = (int) ($left->getAttribute($column) ?? 0);

                if ($rightValue !== $leftValue) {
                    return $rightValue <=> $leftValue;
                }
            }

            return strcasecmp(
                (string) $left->getAttribute('title'),
                (string) $right->getAttribute('title'),
            );
        });

        return new EloquentCollection($sorted->values()->all());
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

    private function countSharedTerms(string $left, string $right): int
    {
        return collect($this->extractTerms($left))
            ->intersect($this->extractTerms($right))
            ->take(3)
            ->count();
    }
}
