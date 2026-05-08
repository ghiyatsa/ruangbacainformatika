import { Skeleton } from '@/components/ui/skeleton';

export function SkripsiGridSkeleton() {
    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {Array.from({ length: 12 }).map((_, i) => (
                <div
                    key={i}
                    className="flex flex-col overflow-hidden rounded-2xl border bg-card"
                >
                    <Skeleton className="h-1.5 w-full rounded-none" />
                    <div className="flex flex-col gap-3 p-5">
                        <div className="flex items-center justify-between">
                            <Skeleton className="h-5 w-16 rounded-full" />
                            <Skeleton className="h-4 w-10 rounded" />
                        </div>
                        <Skeleton className="h-4 w-full rounded" />
                        <Skeleton className="h-4 w-4/5 rounded" />
                        <Skeleton className="h-4 w-3/5 rounded" />
                        <div className="flex gap-1">
                            <Skeleton className="h-5 w-14 rounded-md" />
                            <Skeleton className="h-5 w-16 rounded-md" />
                        </div>
                        <div className="mt-1 border-t pt-3">
                            <Skeleton className="h-4 w-32 rounded" />
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}
