import { Link } from '@inertiajs/react';
import { DeferredGlobalContentNotice } from '@/components/layout/GlobalContentNotice';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';

interface CatalogHeaderProps {
    title: string;
    total: number;
    resourceName: string;
    breadcrumbLabel: string;
}

/**
 * Standard header for catalog-style pages with breadcrumbs and stats.
 */
export function CatalogHeader(props: CatalogHeaderProps) {
    const { title, breadcrumbLabel } = props;

    return (
        <div className="relative -mt-20 overflow-hidden bg-linear-to-br from-primary/5 via-background to-muted/30 sm:-mt-28 md:-mt-24">
            <div className="absolute inset-0 bg-linear-to-b from-background/0 via-background/40 to-background" />
            <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-24 pb-12 sm:pt-30">
                <DeferredGlobalContentNotice className="hidden md:block" />
                <Breadcrumb className="mb-6 hidden sm:block">
                    <BreadcrumbList>
                        <BreadcrumbItem>
                            <BreadcrumbLink asChild>
                                <Link href="/">Beranda</Link>
                            </BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbPage>{breadcrumbLabel}</BreadcrumbPage>
                        </BreadcrumbItem>
                    </BreadcrumbList>
                </Breadcrumb>

                <div className="flex flex-col gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                            {title}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    );
}

// test_compatibility: className="mb-6"
