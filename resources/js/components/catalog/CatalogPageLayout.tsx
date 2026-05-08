import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

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
        <>
            <Head title={title} />

            {/* Dot-grid background */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            <div className="relative z-10">
                {header}

                <main className="pb-16">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <div className="flex flex-col gap-8 md:gap-10">
                            {children}
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
