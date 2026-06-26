import { InfiniteScroll } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';
import type { PaginationData } from '@/types/pagination';

interface CatalogMobilePaginationProps<T> {
    data?: PaginationData<T>;
    propKey: string;
    resourceLabel: string;
    loadingFallback?: ReactNode;
    className?: string;
    completeLabel?: string;
}

export function CatalogMobilePagination<T>({
    data,
    propKey,
    loadingFallback,
    className,
    completeLabel = 'Semua daftar telah ditampilkan.',
}: CatalogMobilePaginationProps<T>) {
    if (!data?.next_page_url) {
        return (
            <div className="py-4 text-center text-sm text-muted-foreground">
                {completeLabel}
            </div>
        );
    }

    return (
        <div className={cn(className, '-mt-8')}>
            <InfiniteScroll data={propKey}>
                {loadingFallback}
            </InfiniteScroll>
        </div>
    );
}
