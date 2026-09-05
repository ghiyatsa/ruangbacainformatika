import { Link } from '@inertiajs/react';
import { Fragment } from 'react';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Skeleton } from '@/components/ui/skeleton';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function Breadcrumbs({
    breadcrumbs,
    loading = false,
}: {
    breadcrumbs?: BreadcrumbItemType[];
    loading?: boolean;
}) {
    if (loading) {
        return (
            <Breadcrumb className="hidden sm:block">
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <Skeleton className="h-4 w-14 animate-pulse rounded-md" />
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <Skeleton className="h-4 w-20 animate-pulse rounded-md" />
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <Skeleton className="h-4 w-32 animate-pulse rounded-md" />
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        );
    }

    if (!breadcrumbs || breadcrumbs.length === 0) {
        return null;
    }

    const renderBreadcrumbItem = (
        item: BreadcrumbItemType,
        index: number,
        items: BreadcrumbItemType[],
        compact = false,
    ) => {
        const isLast = index === items.length - 1;
        const textClassName = compact
            ? 'inline-block max-w-[12rem] truncate align-bottom'
            : undefined;

        const content = item.title ?? (
            <Skeleton className="h-4 w-24 animate-pulse rounded-md" />
        );

        return (
            <Fragment key={`breadcrumb-${index}`}>
                <BreadcrumbItem>
                    {isLast ? (
                        <BreadcrumbPage className={textClassName}>
                            {content}
                        </BreadcrumbPage>
                    ) : (
                        <BreadcrumbLink asChild className={textClassName}>
                            <Link href={item.href}>{content}</Link>
                        </BreadcrumbLink>
                    )}
                </BreadcrumbItem>
                {!isLast && <BreadcrumbSeparator />}
            </Fragment>
        );
    };

    return (
        <Breadcrumb className="hidden sm:block">
            <BreadcrumbList>
                {breadcrumbs.map((item, index) =>
                    renderBreadcrumbItem(item, index, breadcrumbs),
                )}
            </BreadcrumbList>
        </Breadcrumb>
    );
}
