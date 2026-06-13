import { Skeleton } from '@/components/ui/skeleton';

export function KtiCardSkeleton() {
    return (
        <div className="flex h-full flex-col overflow-hidden rounded-xl border bg-card">
            <div className="flex flex-col gap-3 p-6">
                <div className="flex items-center justify-between gap-4">
                    <Skeleton className="h-6 w-20 rounded-full" />
                    <Skeleton className="h-3.5 w-12" />
                </div>

                <div className="min-h-[3.75rem] space-y-2">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-11/12" />
                    <Skeleton className="h-4 w-4/5" />
                </div>
            </div>

            <div className="flex flex-1 flex-col gap-3 px-6 pb-6">
                <div className="min-h-[1.5rem]">
                    <div className="flex flex-wrap gap-1">
                        <Skeleton className="h-5 w-16" />
                        <Skeleton className="h-5 w-20" />
                        <Skeleton className="h-5 w-14" />
                    </div>
                </div>
            </div>

            <div className="mt-auto flex min-h-[4.5rem] items-center justify-between border-t p-6">
                <div className="flex flex-col gap-2">
                    <Skeleton className="h-4 w-32" />
                    <Skeleton className="h-3 w-20" />
                </div>
                <Skeleton className="size-4 rounded-full" />
            </div>
        </div>
    );
}

