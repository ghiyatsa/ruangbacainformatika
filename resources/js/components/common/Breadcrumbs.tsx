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
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <Skeleton className="h-4 w-14 animate-pulse rounded-md" />
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem className="hidden sm:inline-flex">
                        <Skeleton className="h-4 w-20 animate-pulse rounded-md" />
                    </BreadcrumbItem>
                    <BreadcrumbSeparator className="hidden sm:inline-flex" />
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

    const renderItem = (item: BreadcrumbItemType, index: number, isLast: boolean) => {
        const textCls = 'inline-block max-w-[10rem] truncate align-bottom sm:max-w-[16rem]';
        const content = item.title ?? <Skeleton className="h-4 w-24 animate-pulse rounded-md" />;

        return (
            <BreadcrumbItem key={`bc-${index}`}>
                {isLast ? (
                    <BreadcrumbPage className={textCls}>{content}</BreadcrumbPage>
                ) : (
                    <BreadcrumbLink asChild className={textCls}>
                        <Link href={item.href}>{content}</Link>
                    </BreadcrumbLink>
                )}
            </BreadcrumbItem>
        );
    };

    // Mobile: first + ellipsis + last (when >2 items)
    // Desktop (sm+): all items
    const first = breadcrumbs[0];
    const last = breadcrumbs[breadcrumbs.length - 1];
    const middle = breadcrumbs.slice(1, -1);
    const isCollapsible = breadcrumbs.length > 2;

    return (
        <Breadcrumb>
            <BreadcrumbList>
                {/* First item — always visible */}
                {renderItem(first, 0, breadcrumbs.length === 1)}
                {breadcrumbs.length > 1 && <BreadcrumbSeparator />}

                {/* Middle items — desktop only */}
                {isCollapsible && middle.map((item, i) => (
                    <Fragment key={`bc-mid-${i}`}>
                        <BreadcrumbItem className="hidden sm:inline-flex">
                            <BreadcrumbLink asChild className="inline-block max-w-[16rem] truncate align-bottom">
                                <Link href={item.href}>{item.title}</Link>
                            </BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator className="hidden sm:inline-flex" />
                    </Fragment>
                ))}

                {/* Ellipsis — mobile only, when middle items are hidden */}
                {isCollapsible && (
                    <Fragment>
                        <BreadcrumbItem className="sm:hidden">
                            <BreadcrumbEllipsis className="size-4" />
                        </BreadcrumbItem>
                        <BreadcrumbSeparator className="sm:hidden" />
                    </Fragment>
                )}

                {/* Last item — always visible */}
                {breadcrumbs.length > 1 && renderItem(last, breadcrumbs.length - 1, true)}
            </BreadcrumbList>
        </Breadcrumb>
    );
}
