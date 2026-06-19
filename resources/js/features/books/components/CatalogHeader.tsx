import { Link } from '@inertiajs/react';
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
        <div className="relative -mt-20 overflow-hidden bg-background sm:-mt-28 md:-mt-24">
            <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-12 sm:px-6 sm:pt-30 lg:px-8">
                <div className="hidden sm:flex sm:items-center border-y border-border/60 py-3 mb-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 bg-muted/5">
                    <Breadcrumb>
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
                </div>

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
