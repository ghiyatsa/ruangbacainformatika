import type { ReactNode } from 'react';
import { PageLayout } from '@/components/layouts/PageLayout';

interface CatalogPageLayoutProps {
    title: string;
    header: ReactNode;
    children: ReactNode;
}

/**
 * Standard layout for catalog-style pages (Books, Skripsi, etc.)
 * Ensures consistent spacing and background across all catalogs.
 */
export function CatalogPageLayout({
    title,
    header,
    children,
}: CatalogPageLayoutProps) {
    return (
        <PageLayout
            title={title}
            header={header}
            maxWidth="7xl"
            className="pb-16 pt-0"
        >
            <div className="flex flex-col gap-8 md:gap-10">
                {children}
            </div>
        </PageLayout>
    );
}
