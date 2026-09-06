import { Skeleton } from '@/components/ui/skeleton';

export function SearchResultsSkeleton() {
    return (
        <div className="space-y-4 p-1">
            {/* Section 1: Books */}
            <div>
                <Skeleton className="mb-2 h-3.5 w-16 rounded" />
                <div className="space-y-1.5">
                    {[1, 2, 3].map((i) => (
                        <div
                            key={i}
                            className="flex items-center gap-3 rounded-lg px-2.5 py-2"
                        >
                            <Skeleton className="size-4 shrink-0 rounded" />
                            <div className="min-w-0 flex-1 space-y-1.5">
                                <Skeleton className="h-4 w-3/4 rounded" />
                                <Skeleton className="h-3 w-1/2 rounded" />
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Section 2: Academic Works */}
            <div>
                <Skeleton className="mb-2 h-3.5 w-24 rounded" />
                <div className="space-y-1.5">
                    {[1, 2].map((i) => (
                        <div
                            key={i}
                            className="flex items-center gap-3 rounded-lg px-2.5 py-2"
                        >
                            <Skeleton className="size-4 shrink-0 rounded" />
                            <div className="min-w-0 flex-1 space-y-1.5">
                                <Skeleton className="h-4 w-4/5 rounded" />
                                <Skeleton className="h-3 w-1/3 rounded" />
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
