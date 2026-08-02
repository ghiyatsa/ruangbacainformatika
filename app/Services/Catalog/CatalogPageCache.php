<?php

namespace App\Services\Catalog;

use App\Http\Resources\BookCatalogResource;
use App\Services\CatalogService;
use Illuminate\Support\Facades\Cache;

class CatalogPageCache
{
    protected const VERSION_KEY = 'catalog:page:version';

    protected const VERSION_TTL_SECONDS = 604800;

    protected const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        protected CatalogQueryService $catalogQueryService,
        protected CatalogService $catalogService,
    ) {}

    /**
     * @param  array{search: string, category: string, author: string, publisher: string, year: int|null, featured: bool, availability: bool}  $filters
     * @return array<string, mixed>
     */
    public function booksIndex(array $filters, int $page, int $perPage): array
    {
        return Cache::remember(
            $this->key('books:'.$this->filtersKey($filters).':page:'.$page.':'.$perPage),
            self::CACHE_TTL_SECONDS,
            function () use ($filters, $page, $perPage): array {
                $books = $this->catalogQueryService->booksQuery($filters)
                    ->paginate($perPage, page: $page)
                    ->withQueryString();

                $paginated = $books->toArray();
                $paginated['data'] = BookCatalogResource::collection($books->getCollection())->resolve();

                return $paginated;
            },
        );
    }

    /**
     * @param  array{search: string, category: string, author: string, publisher: string, year: int|null, featured: bool, availability: bool}  $filters
     */
    public function searchResultsCount(array $filters): int
    {
        return (int) Cache::remember(
            $this->key('count:'.$this->filtersKey($filters)),
            self::CACHE_TTL_SECONDS,
            fn (): int => $this->catalogQueryService->booksQuery($filters)->count(),
        );
    }

    /**
     * @return array<int, int>
     */
    public function years(): array
    {
        return Cache::remember($this->key('years'), self::CACHE_TTL_SECONDS, fn (): array => $this->catalogQueryService->years());
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, description: string|null, booksCount: int}>
     */
    public function categories(): array
    {
        return Cache::remember($this->key('categories'), self::CACHE_TTL_SECONDS, fn (): array => $this->catalogService->getCategoriesWithCounts()->all());
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, booksCount: int}>
     */
    public function authors(): array
    {
        return Cache::remember($this->key('authors'), self::CACHE_TTL_SECONDS, fn (): array => $this->catalogQueryService->authors());
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, booksCount: int}>
     */
    public function publishers(): array
    {
        return Cache::remember($this->key('publishers'), self::CACHE_TTL_SECONDS, fn (): array => $this->catalogQueryService->publishers());
    }

    public function invalidate(): void
    {
        Cache::put(self::VERSION_KEY, $this->version() + 1, now()->addSeconds(self::VERSION_TTL_SECONDS));
    }

    protected function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 0);
    }

    protected function key(string $name): string
    {
        return 'catalog:page:'.$this->version().':'.$name;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filtersKey(array $filters): string
    {
        return md5(serialize($filters));
    }
}
