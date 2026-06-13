import { PageLayout } from '@/components/layout/PageLayout';
import { CatalogHeader } from '@/features/books/components/CatalogHeader';
import { CatalogPagination } from '@/features/books/components/CatalogPagination';
import type { ReactNode } from 'react';
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
    paginationVisibility?: 'all' | 'desktop-only' | 'none';
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
    paginationVisibility = 'all',
}: CatalogPageLayoutProps<T>) {
    const defaultHeader = (
        <CatalogHeader
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
            showDesktopNoticeInContent={false}
        >
            <div className="relative z-10 flex flex-col gap-6 md:gap-8 -mt-6 sm:-mt-8">
                {children}

                {paginationData && paginationVisibility !== 'none' && (
                    <div
                        className={`mt-8 ${paginationVisibility === 'desktop-only' ? 'hidden md:block' : ''}`}
                    >
                        <CatalogPagination
                            data={paginationData}
                            resourceName={resourceName}
                        />
                    </div>
                )}
            </div>
        </PageLayout>
    );
}
