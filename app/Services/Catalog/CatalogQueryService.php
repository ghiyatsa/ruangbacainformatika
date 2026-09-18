<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Services\Search\SearchTermCorrector;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogQueryService
{
    /**
     * @var array<int, string>
     */
    protected const BOOK_LIST_COLUMNS = [
        'id',
        'title',
        'slug',
        'description',
        'cover_image',
        'published_year',
        'pages',
        'is_featured',
        'is_borrowable',
        'view_count',
    ];

    /**
     * @return array{
     *     search: string,
     *     category: string,
     *     author: string,
     *     publisher: string,
     *     year: int|null,
     *     featured: bool,
     *     availability: bool
     * }
     */
    public function filtersFromRequest(Request $request): array
    {
        return [
            'search' => str($request->string('search')->toString())
                ->squish()
                ->limit(100, '')
                ->toString(),
            'category' => $request->string('category')->toString(),
            'author' => $request->string('author')->toString(),
            'publisher' => $request->string('publisher')->toString(),
            'year' => $request->integer('year') ?: null,
            'featured' => $request->boolean('featured'),
            'availability' => $request->boolean('availability'),
        ];
    }

    /**
     * @param  array{search: string, category: string, author: string, publisher: string, year: int|null, featured: bool, availability: bool}  $filters
     * @return Builder<Book>
     */

    /** Ambang jumlah hasil yang dianggap terlalu sedikit. */
    protected const MIN_ACCEPTABLE_RESULTS = 3;

    /**
     * Tentukan kata kunci yang benar-benar dipakai untuk pencarian.
     *
     * Bila kueri asli menghasilkan terlalu sedikit hasil, kueri dicoba
     * dikoreksi ejaannya. Koreksi hanya dipakai bila benar-benar menambah
     * hasil, sehingga pencarian yang sudah baik tidak pernah memburuk.
     *
     * @param  array{search: string, category: string, author: string, publisher: string, year: int|null, featured: bool, availability: bool}  $filters
     */
    protected function resolveSearchTerm(array $filters): string
    {
        $search = $filters['search'];

        if ($search === '' || mb_strlen($search) < 4) {
            return $search;
        }

        $asli = $this->countForSearch($filters, $search);

        if ($asli >= self::MIN_ACCEPTABLE_RESULTS) {
            return $search;
        }

        $koreksi = app(SearchTermCorrector::class)->correctQuery($search);

        if ($koreksi === null || $koreksi === $search) {
            return $search;
        }

        return $this->countForSearch($filters, $koreksi) > $asli ? $koreksi : $search;
    }

    /**
     * Hitung hasil untuk sebuah kata kunci tanpa mengubah filter lain.
     *
     * @param  array{search: string, category: string, author: string, publisher: string, year: int|null, featured: bool, availability: bool}  $filters
     */
    protected function countForSearch(array $filters, string $search): int
    {
        return Book::query()
            ->published()
            ->search($search)
            ->forCategory($filters['category'])
            ->forAuthor($filters['author'])
            ->forPublisher($filters['publisher'])
            ->forYear($filters['year'])
            ->when($filters['featured'], fn ($query) => $query->featured())
            ->onlyAvailable($filters['availability'])
            ->count();
    }

    public function booksQuery(array $filters): Builder
    {
        return Book::query()
            ->published()
            ->search($this->resolveSearchTerm($filters))
            ->forCategory($filters['category'])
            ->forAuthor($filters['author'])
            ->forPublisher($filters['publisher'])
            ->forYear($filters['year'])
            ->when($filters['featured'], fn ($query) => $query->featured())
            ->onlyAvailable($filters['availability'])
            ->select(self::BOOK_LIST_COLUMNS)
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount([
                'items',
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->when(
                $this->resolveSearchTerm($filters) !== '',
                fn ($query) => $this->orderByTitleRelevance($query, $this->resolveSearchTerm($filters)),
            )
            ->orderByRaw('CASE WHEN cover_image IS NOT NULL THEN 0 ELSE 1 END')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_year')
            ->orderBy('title');
    }

    /**
     * @return array<int, int>
     */
    public function years(): array
    {
        return Book::published()
            ->whereNotNull('published_year')
            ->distinct()
            ->orderByDesc('published_year')
            ->pluck('published_year')
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, booksCount: int}>
     */
    public function authors(): array
    {
        return Author::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('books', fn ($query) => $query->published())
            ->withCount([
                'books' => fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Author $author) => [
                'id' => $author->id,
                'name' => $author->name,
                'slug' => $author->slug,
                'booksCount' => $author->books_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, booksCount: int}>
     */
    public function publishers(): array
    {
        return Publisher::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('books', fn ($query) => $query->published())
            ->withCount([
                'books' => fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Publisher $publisher) => [
                'id' => $publisher->id,
                'name' => $publisher->name,
                'slug' => $publisher->slug,
                'booksCount' => $publisher->books_count,
            ])
            ->all();
    }

    /**
     * @param  array{category: string, author: string, publisher: string}  $filters
     * @return array{category: string|null, author: string|null, publisher: string|null}
     */
    public function activeFilterLabels(array $filters): array
    {
        return [
            'category' => $this->resolveCategoryLabel($filters['category']),
            'author' => $this->resolveAuthorLabel($filters['author']),
            'publisher' => $this->resolvePublisherLabel($filters['publisher']),
        ];
    }

    protected function resolveCategoryLabel(string $categorySlug): ?string
    {
        if ($categorySlug === '') {
            return null;
        }

        return Category::query()
            ->where('slug', $categorySlug)
            ->value('name');
    }

    protected function resolveAuthorLabel(string $authorSlug): ?string
    {
        if ($authorSlug === '') {
            return null;
        }

        return Author::query()
            ->where('slug', $authorSlug)
            ->value('name');
    }

    protected function resolvePublisherLabel(string $publisherSlug): ?string
    {
        if ($publisherSlug === '') {
            return null;
        }

        return Publisher::query()
            ->where('slug', $publisherSlug)
            ->value('name');
    }

    /**
     * Beri bobot pada judul agar karya yang judulnya memuat kata kunci naik.
     *
     * Setiap kata kunci yang muncul di judul menambah skor. Frasa utuh di
     * judul diberi tambahan, dan judul yang diawali kata kunci diberi
     * tambahan lagi. Buku yang judulnya tidak memuat kata kunci tetap
     * ditampilkan, hanya berada di bawah.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    protected function orderByTitleRelevance(Builder $query, string $search): Builder
    {
        $judul = 'LOWER(COALESCE(title, \'\'))';
        $skor = '0';
        $frasa = mb_strtolower(trim($search));

        // Setiap kata menambah skor bila muncul di judul.
        foreach (preg_split('/\s+/u', $frasa, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $kata) {
            if (mb_strlen($kata) < 2) {
                continue;
            }

            $aman = addslashes($kata);
            $skor .= " + (CASE WHEN {$judul} LIKE '%{$aman}%' THEN 10 ELSE 0 END)";
        }

        // Frasa utuh dan judul yang diawali kata kunci bernilai lebih tinggi.
        $aman2 = addslashes($frasa);
        $skor .= " + (CASE WHEN {$judul} LIKE '%{$aman2}%' THEN 25 ELSE 0 END)";
        $skor .= " + (CASE WHEN {$judul} LIKE '{$aman2}%' THEN 40 ELSE 0 END)";

        return $query->orderByRaw("({$skor}) DESC");
    }
}
