import { Skeleton } from '@/components/ui/skeleton';

export function BookCatalogFiltersSkeleton() {
    return (
        <div className="flex flex-col gap-4" aria-hidden="true">
            {/* Top row: Results stats & View toggle */}
            <div className="flex items-center justify-between gap-3">
                <Skeleton className="h-8 w-24 rounded-xl" />
                <Skeleton className="hidden h-9 w-20 rounded-lg sm:block" />
            </div>

            {/* Bottom row: Filter selectors */}
            <div className="flex flex-wrap items-center justify-between gap-3 sm:justify-start">
                {/* Category Filter */}
                <div className="flex w-[calc(50%-6px)] flex-col gap-1.5 sm:w-auto sm:flex-none">
                    <Skeleton className="h-10 w-full rounded-lg sm:w-48" />
                </div>

                {/* Author Filter */}
                <div className="flex w-[calc(50%-6px)] flex-col gap-1.5 sm:w-auto sm:flex-none">
                    <Skeleton className="h-10 w-full rounded-lg sm:w-48" />
                </div>

                {/* Publisher Filter */}
                <div className="flex w-[calc(50%-6px)] flex-col gap-1.5 sm:w-auto sm:flex-none">
                    <Skeleton className="h-10 w-full rounded-lg sm:w-48" />
                </div>

                {/* Year Filter */}
                <div className="flex w-[calc(50%-6px)] items-center gap-2 sm:w-auto sm:flex-none">
                    <Skeleton className="h-10 w-full rounded-lg sm:w-32" />
                </div>

                {/* Checkboxes container */}
                <div className="flex h-10 w-full items-center justify-center gap-2 rounded-xl border bg-background/70 px-2 py-2 sm:w-auto sm:flex-none sm:gap-3 sm:px-3 sm:justify-start">
                    <div className="flex items-center gap-2">
                        <Skeleton className="size-4 rounded-sm" />
                        <Skeleton className="h-3 w-16 rounded-xs" />
                    </div>
                    <div className="h-5 w-px bg-border" />
                    <div className="flex items-center gap-2">
                        <Skeleton className="size-4 rounded-sm" />
                        <Skeleton className="h-3 w-16 rounded-xs" />
                    </div>
                </div>
            </div>
        </div>
    );
}

export default BookCatalogFiltersSkeleton;
