import { Deferred } from '@inertiajs/react';
import { CatalogPageLayout } from '@/components/catalog/CatalogPageLayout';
import type { ReactNode } from 'react';
import type { CatalogActiveFilters } from '@/components/catalog/types';
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
    };
    onClearFilters?: () => void;
    onRemoveFilter?: (key: string) => void;
    filtersPanel?: ReactNode;
    deferredData?: string;
    loadingFallback?: ReactNode;
    paginationVisibility?: 'all' | 'desktop-only';
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

    return (
        <CatalogPageLayout
            title={title}
            metaDescription={metaDescription}
            resourceName={resourceName}
            breadcrumbLabel={breadcrumbLabel}
            totalCount={totalCount}
            paginationData={paginationData}
            filters={filters}
            filterLabels={filterLabels}
            onClearFilters={onClearFilters}
            onRemoveFilter={onRemoveFilter}
            paginationVisibility={paginationVisibility}
        >
            {filtersPanel ? (
                <div className="flex flex-col gap-6">{filtersPanel}</div>
            ) : null}

            {content}
        </CatalogPageLayout>
    );
}
