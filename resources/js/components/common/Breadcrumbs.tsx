import { Link } from '@inertiajs/react';
import { Fragment } from 'react';
import {
    Breadcrumb,
    BreadcrumbEllipsis,
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
            <Breadcrumb className="min-w-0">
                <BreadcrumbList className="flex-nowrap">
                    <BreadcrumbItem className="shrink-0">
                        <Skeleton className="h-4 w-14 animate-pulse rounded-md" />
                    </BreadcrumbItem>
                    <BreadcrumbSeparator className="shrink-0" />
                    <BreadcrumbItem className="hidden shrink-0 sm:inline-flex">
                        <Skeleton className="h-4 w-20 animate-pulse rounded-md" />
                    </BreadcrumbItem>
                    <BreadcrumbSeparator className="hidden shrink-0 sm:inline-flex" />
                    <BreadcrumbItem className="min-w-0">
                        <Skeleton className="h-4 w-32 animate-pulse rounded-md" />
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        );
    }

    if (!breadcrumbs || breadcrumbs.length === 0) {
        return null;
    }

    const first = breadcrumbs[0];
    const last = breadcrumbs[breadcrumbs.length - 1];
    const middle = breadcrumbs.slice(1, -1);
    const isCollapsible = breadcrumbs.length > 2;
    const isSingle = breadcrumbs.length === 1;

    const renderNonLastItem = (item: BreadcrumbItemType) => (
        <BreadcrumbLink asChild className="truncate">
            <Link href={item.href}>{item.title}</Link>
        </BreadcrumbLink>
    );

    return (
        <Breadcrumb className="min-w-0">
            {/* flex-nowrap + min-w-0 agar last item bisa truncate otomatis */}
            <BreadcrumbList className="flex-nowrap">
                {/* First item — shrink-0 agar tidak ikut menyusut */}
                <BreadcrumbItem className={isSingle ? 'min-w-0' : 'shrink-0'}>
                    {isSingle ? (
                        <BreadcrumbPage className="block truncate">{first.title}</BreadcrumbPage>
                    ) : (
                        renderNonLastItem(first)
                    )}
                </BreadcrumbItem>

                {!isSingle && <BreadcrumbSeparator className="shrink-0" />}

                {/* Middle items — desktop only, shrink-0 */}
                {isCollapsible && middle.map((item, i) => (
                    <Fragment key={`bc-mid-${i}`}>
                        <BreadcrumbItem className="hidden shrink-0 sm:inline-flex">
                            {renderNonLastItem(item)}
                        </BreadcrumbItem>
                        <BreadcrumbSeparator className="hidden shrink-0 sm:inline-flex" />
                    </Fragment>
                ))}

                {/* Ellipsis — mobile only */}
                {isCollapsible && (
                    <Fragment>
                        <BreadcrumbItem className="shrink-0 sm:hidden">
                            <BreadcrumbEllipsis className="size-4" />
                        </BreadcrumbItem>
                        <BreadcrumbSeparator className="shrink-0 sm:hidden" />
                    </Fragment>
                )}

                {/* Last item — min-w-0 + truncate, mengisi sisa ruang */}
                {!isSingle && (
                    <BreadcrumbItem className="min-w-0">
                        <BreadcrumbPage className="block truncate">
                            {last.title ?? <Skeleton className="h-4 w-24 animate-pulse rounded-md" />}
                        </BreadcrumbPage>
                    </BreadcrumbItem>
                )}
            </BreadcrumbList>
        </Breadcrumb>
    );
}
