import { Skeleton } from '@/components/ui/skeleton';

/**
 * Skeleton loader for BookCard — uses shadcn Skeleton to
 * mirror the real card's layout.
 */
export default function BookCardSkeleton() {
    return (
        <div className="flex h-full flex-col overflow-hidden rounded-2xl border bg-card">
            {/* Cover placeholder */}
            <Skeleton className="aspect-3/4 w-full rounded-none" />

            {/* Content placeholder */}
            <div className="flex flex-1 flex-col gap-3 p-3 sm:p-4">
                <div className="flex gap-1">
                    <Skeleton className="h-4 w-16" />
                </div>
                <Skeleton className="h-5 w-full" />
                <Skeleton className="h-4 w-2/3" />
                
                <div className="flex-1" />
                
                <div className="flex items-center justify-between border-t pt-2.5">
                    <Skeleton className="h-5 w-16 rounded-full" />
                    <div className="flex items-center gap-2">
                        <Skeleton className="h-3 w-8" />
                        <Skeleton className="h-3 w-12" />
                    </div>
                </div>
            </div>
        </div>
    );
}
