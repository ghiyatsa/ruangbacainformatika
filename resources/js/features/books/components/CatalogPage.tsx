import { Deferred } from '@inertiajs/react';
import { Calendar, CheckCircle, Library, Search, Star, X } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CatalogPageLayout } from '@/features/books/components/CatalogPageLayout';
import type { ReactNode } from 'react';
import type { CatalogActiveFilters } from '@/features/books/components/types';
import type { PaginationData } from '@/types/pagination';

interface CatalogPageProps<T> {
    title: string;
    metaDescription?: string;
    resourceName: string;
    breadcrumbLabel: string;
    totalCount: number;
    paginationData?: PaginationData<T>;
    filters?: CatalogActiveFilters;
    filterLabels?: {
        category?: string;
        author?: string;
        publisher?: string;
    };
    onClearFilters?: () => void;
    onRemoveFilter?: (key: string) => void;
    filtersPanel?: ReactNode;
    deferredData?: string;
    loadingFallback?: ReactNode;
    paginationVisibility?: 'all' | 'desktop-only' | 'none';
    children: ReactNode;
}

export function CatalogPage<T>({
    title,
    metaDescription,
    resourceName,
    breadcrumbLabel,
    totalCount,
    paginationData,
    filters,
    filterLabels,
    onClearFilters,
    onRemoveFilter,
    filtersPanel,
    deferredData,
    loadingFallback,
    paginationVisibility,
    children,
}: CatalogPageProps<T>) {
    const content =
        deferredData && loadingFallback ? (
            <Deferred data={deferredData} fallback={loadingFallback}>
                {children}
            </Deferred>
        ) : (
            children
        );

    const hasActiveFilters =
        filters &&
        (filters.search ||
            filters.category ||
            filters.author ||
            filters.publisher ||
            filters.year ||
            filters.featured ||
            filters.availability);

    return (
        <CatalogPageLayout
            title={title}
            metaDescription={metaDescription}
            resourceName={resourceName}
            breadcrumbLabel={breadcrumbLabel}
            totalCount={totalCount}
            paginationData={paginationData}
            paginationVisibility={paginationVisibility}
        >
            {(hasActiveFilters || filtersPanel) && (
                <div className="flex flex-col gap-2">
                    {hasActiveFilters && onClearFilters && (
                        <div className="flex flex-wrap items-center gap-2">
                            {/* Search Badge */}
                            {filters.search && (
                                <Badge
                                    variant="secondary"
                                    className="gap-1.5 py-1.5 pr-2 pl-2.5"
                                >
                                    <Search className="size-3 text-muted-foreground" />
                                    <span className="text-muted-foreground">
                                        Pencarian:
                                    </span>
                                    &ldquo;{filters.search}&rdquo;
                                    {onRemoveFilter && (
                                        <button
                                            type="button"
                                            aria-label="Hapus filter pencarian"
                                            onClick={() => onRemoveFilter('search')}
                                            className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                                        >
                                            <X className="size-3" />
                                        </button>
                                    )}
                                </Badge>
                            )}

                            {/* Category Badge */}
                            {filters.category && (
                                <Badge
                                    variant="secondary"
                                    className="gap-1.5 py-1.5 pr-2 pl-2.5"
                                >
                                    <Library className="size-3 text-muted-foreground" />
                                    <span className="text-muted-foreground">
                                        Kategori:
                                    </span>
                                    {filterLabels?.category ?? filters.category}
                                    {onRemoveFilter && (
                                        <button
                                            type="button"
                                            aria-label="Hapus filter kategori"
                                            onClick={() =>
                                                onRemoveFilter('category')
                                            }
                                            className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                                        >
                                            <X className="size-3" />
                                        </button>
                                    )}
                                </Badge>
                            )}

                            {/* Author Badge */}
                            {filters.author && (
                                <Badge
                                    variant="secondary"
                                    className="gap-1.5 py-1.5 pr-2 pl-2.5"
                                >
                                    <span className="text-muted-foreground">
                                        Penulis:
                                    </span>
                                    {filterLabels?.author ?? filters.author}
                                    {onRemoveFilter && (
                                        <button
                                            type="button"
                                            aria-label="Hapus filter penulis"
                                            onClick={() =>
                                                onRemoveFilter('author')
                                            }
                                            className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                                        >
                                            <X className="size-3" />
                                        </button>
                                    )}
                                </Badge>
                            )}

                            {/* Publisher Badge */}
                            {filters.publisher && (
                                <Badge
                                    variant="secondary"
                                    className="gap-1.5 py-1.5 pr-2 pl-2.5"
                                >
                                    <span className="text-muted-foreground">
                                        Penerbit:
                                    </span>
                                    {filterLabels?.publisher ?? filters.publisher}
                                    {onRemoveFilter && (
                                        <button
                                            type="button"
                                            aria-label="Hapus filter penerbit"
                                            onClick={() =>
                                                onRemoveFilter('publisher')
                                            }
                                            className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                                        >
                                            <X className="size-3" />
                                        </button>
                                    )}
                                </Badge>
                            )}

                            {/* Year Badge */}
                            {filters.year && (
                                <Badge
                                    variant="secondary"
                                    className="gap-1.5 py-1.5 pr-2 pl-2.5"
                                >
                                    <Calendar className="size-3 text-muted-foreground" />
                                    <span className="text-muted-foreground">
                                        Tahun:
                                    </span>
                                    {filters.year}
                                    {onRemoveFilter && (
                                        <button
                                            type="button"
                                            aria-label="Hapus filter tahun"
                                            onClick={() => onRemoveFilter('year')}
                                            className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                                        >
                                            <X className="size-3" />
                                        </button>
                                    )}
                                </Badge>
                            )}

                            {/* Featured Badge */}
                            {filters.featured && (
                                <Badge
                                    variant="secondary"
                                    className="gap-1.5 py-1.5 pr-2 pl-2.5"
                                >
                                    <Star className="size-3 fill-amber-500 text-amber-500" />
                                    <span className="text-muted-foreground">
                                        Unggulan
                                    </span>
                                    {onRemoveFilter && (
                                        <button
                                            type="button"
                                            aria-label="Hapus filter unggulan"
                                            onClick={() =>
                                                onRemoveFilter('featured')
                                            }
                                            className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                                        >
                                            <X className="size-3" />
                                        </button>
                                    )}
                                </Badge>
                            )}

                            {/* Availability Badge */}
                            {filters.availability && (
                                <Badge
                                    variant="secondary"
                                    className="gap-1.5 py-1.5 pr-2 pl-2.5"
                                >
                                    <CheckCircle className="size-3 text-emerald-500" />
                                    <span className="text-muted-foreground">
                                        Tersedia
                                    </span>
                                    {onRemoveFilter && (
                                        <button
                                            type="button"
                                            aria-label="Hapus filter ketersediaan"
                                            onClick={() =>
                                                onRemoveFilter('availability')
                                            }
                                            className="ml-1 rounded-full p-0.5 transition-colors hover:bg-muted"
                                        >
                                            <X className="size-3" />
                                        </button>
                                    )}
                                </Badge>
                            )}

                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-8 text-xs text-muted-foreground"
                                onClick={onClearFilters}
                            >
                                Hapus semua
                            </Button>
                        </div>
                    )}
                    {filtersPanel}
                </div>
            )}
            {content}
        </CatalogPageLayout>
    );
}
