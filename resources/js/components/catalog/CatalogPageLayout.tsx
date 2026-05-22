import { Calendar, CheckCircle, Search, Star, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { ResourceCatalogHeader } from '@/components/catalog/ResourceCatalogHeader';
import { ResourcePagination } from '@/components/catalog/ResourcePagination';
import type { CatalogActiveFilters } from '@/components/catalog/types';
import { PageLayout } from '@/components/layouts/PageLayout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { PaginationData } from '@/types/pagination';

interface CatalogPageLayoutProps<T> {
    title: string;
    metaDescription?: string;
    resourceName: string;
    breadcrumbLabel: string;
    totalCount: number;
    paginationData?: PaginationData<T>;
    header?: ReactNode; // Optional override
    children: ReactNode;
    filters?: CatalogActiveFilters;
    onClearFilters?: () => void;
    onRemoveFilter?: (key: string) => void;
    paginationVisibility?: 'all' | 'desktop-only';
}

/**
 * Standard layout for catalog-style pages (Books, Skripsi, etc.)
 * Ensures consistent spacing and background across all catalogs.
 */
export function CatalogPageLayout<T>({
    title,
    metaDescription,
    resourceName,
    breadcrumbLabel,
    totalCount,
    paginationData,
    header,
    children,
    filters,
    onClearFilters,
    onRemoveFilter,
    paginationVisibility = 'all',
}: CatalogPageLayoutProps<T>) {
    const hasActiveFilters =
        filters &&
        (filters.search ||
            filters.year ||
            filters.featured ||
            filters.availability);

    const defaultHeader = (
        <ResourceCatalogHeader
            title={title}
            total={totalCount}
            resourceName={resourceName}
            breadcrumbLabel={breadcrumbLabel}
        />
    );

    return (
        <PageLayout
            title={title}
            metaDescription={metaDescription}
            header={header ?? defaultHeader}
            maxWidth="7xl"
            className="pt-0 pb-16"
        >
            <div className="flex flex-col gap-8 md:gap-10">
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
                {children}

                {paginationData && (
                    <div
                        className={`mt-8 ${paginationVisibility === 'desktop-only' ? 'hidden md:block' : ''}`}
                    >
                        <ResourcePagination
                            data={paginationData}
                            resourceName={resourceName}
                        />
                    </div>
                )}
            </div>
        </PageLayout>
    );
}
