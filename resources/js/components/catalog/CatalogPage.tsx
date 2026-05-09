import { Deferred } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { CatalogPageLayout } from '@/components/catalog/CatalogPageLayout';
import type { CatalogActiveFilters } from '@/components/catalog/types';
import type { PaginationData } from '@/types/pagination';

interface CatalogPageProps<T> {
    title: string;
    resourceName: string;
    breadcrumbLabel: string;
    totalCount: number;
    paginationData?: PaginationData<T>;
    filters?: CatalogActiveFilters;
    onClearFilters?: () => void;
    onRemoveFilter?: (key: string) => void;
    filtersPanel?: ReactNode;
    deferredData: string;
    fallback: ReactNode;
    children: ReactNode;
}

export function CatalogPage<T>({
    title,
    resourceName,
    breadcrumbLabel,
    totalCount,
    paginationData,
    filters,
    onClearFilters,
    onRemoveFilter,
    filtersPanel,
    deferredData,
    fallback,
    children,
}: CatalogPageProps<T>) {
    return (
        <CatalogPageLayout
            title={title}
            resourceName={resourceName}
            breadcrumbLabel={breadcrumbLabel}
            totalCount={totalCount}
            paginationData={paginationData}
            filters={filters}
            onClearFilters={onClearFilters}
            onRemoveFilter={onRemoveFilter}
        >
            {filtersPanel ? (
                <div className="flex flex-col gap-6">{filtersPanel}</div>
            ) : null}

            <Deferred data={deferredData} fallback={fallback}>
                {children}
            </Deferred>
        </CatalogPageLayout>
    );
}
