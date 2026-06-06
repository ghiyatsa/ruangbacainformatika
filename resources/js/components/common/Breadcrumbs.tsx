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
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function Breadcrumbs({
    breadcrumbs,
}: {
    breadcrumbs: BreadcrumbItemType[];
}) {
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

        return (
            <Fragment key={`breadcrumb-${index}`}>
                <BreadcrumbItem>
                    {isLast ? (
                        <BreadcrumbPage className={textClassName}>
                            {item.title}
                        </BreadcrumbPage>
                    ) : (
                        <BreadcrumbLink asChild className={textClassName}>
                            <Link href={item.href}>{item.title}</Link>
                        </BreadcrumbLink>
                    )}
                </BreadcrumbItem>
                {!isLast && <BreadcrumbSeparator />}
            </Fragment>
        );
    };

    return (
        <>
            {breadcrumbs.length > 0 && (
                <Breadcrumb className="hidden sm:block">
                    <BreadcrumbList>
                        {breadcrumbs.map((item, index) =>
                            renderBreadcrumbItem(item, index, breadcrumbs),
                        )}
                    </BreadcrumbList>
                </Breadcrumb>
            )}
        </>
    );
}
