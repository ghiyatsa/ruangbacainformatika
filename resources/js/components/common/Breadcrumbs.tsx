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
    const mobileBreadcrumbs = breadcrumbs.slice(-2);
    const mobileParentBreadcrumb = mobileBreadcrumbs.at(0);
    const mobileCurrentBreadcrumb = mobileBreadcrumbs.at(-1);

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
            <Fragment key={`${item.title}-${index}`}>
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
                <Breadcrumb>
                    <BreadcrumbList className="flex w-full min-w-0 flex-nowrap items-center overflow-hidden whitespace-nowrap sm:hidden">
                        {mobileParentBreadcrumb &&
                        mobileCurrentBreadcrumb &&
                        mobileBreadcrumbs.length > 1 ? (
                            <>
                                <BreadcrumbItem className="shrink-0">
                                    <BreadcrumbLink
                                        asChild
                                        className="inline-block max-w-[7rem] truncate align-bottom"
                                    >
                                        <Link
                                            href={mobileParentBreadcrumb.href}
                                        >
                                            {mobileParentBreadcrumb.title}
                                        </Link>
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator className="shrink-0" />
                                <BreadcrumbItem className="min-w-0 flex-1">
                                    <BreadcrumbPage className="block truncate">
                                        {mobileCurrentBreadcrumb.title}
                                    </BreadcrumbPage>
                                </BreadcrumbItem>
                            </>
                        ) : mobileCurrentBreadcrumb ? (
                            <BreadcrumbItem className="min-w-0 flex-1">
                                <BreadcrumbPage className="block truncate">
                                    {mobileCurrentBreadcrumb.title}
                                </BreadcrumbPage>
                            </BreadcrumbItem>
                        ) : null}
                    </BreadcrumbList>

                    <BreadcrumbList className="hidden sm:flex">
                        {breadcrumbs.map((item, index) =>
                            renderBreadcrumbItem(item, index, breadcrumbs),
                        )}
                    </BreadcrumbList>
                </Breadcrumb>
            )}
        </>
    );
}
