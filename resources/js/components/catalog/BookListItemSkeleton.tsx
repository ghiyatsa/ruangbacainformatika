import { Skeleton } from '@/components/ui/skeleton';

export default function BookListItemSkeleton() {
    return (
        <div className="flex items-center gap-4 px-4 py-3">
            <Skeleton className="h-16 w-11 shrink-0 rounded-md" />

            <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                <div className="flex flex-wrap items-center gap-1.5">
                    <Skeleton className="h-3 w-12" />
                    <Skeleton className="h-3 w-16" />
                </div>
                <Skeleton className="h-4 w-3/4" />
                <Skeleton className="h-3 w-1/2" />
            </div>

            <div className="shrink-0">
                <Skeleton className="h-4 w-14 rounded-full" />
            </div>

            <div className="hidden shrink-0 items-center gap-1 sm:flex">
                <Skeleton className="h-3 w-12" />
            </div>
        </div>
    );
}
