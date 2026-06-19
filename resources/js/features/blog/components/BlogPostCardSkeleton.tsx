import { Skeleton } from '@/components/ui/skeleton';

interface BlogPostCardSkeletonProps {
    variant?: 'featured' | 'card' | 'popular';
}

export function BlogPostCardSkeleton({ variant = 'card' }: BlogPostCardSkeletonProps) {
    if (variant === 'featured') {
        return (
            <div className="relative overflow-hidden rounded-2xl border border-border/60 bg-card aspect-square sm:aspect-video w-full">
                <Skeleton className="h-full w-full" />
                <div className="absolute right-0 bottom-0 left-0 p-5 sm:p-6 space-y-3">
                    <div className="flex gap-1.5">
                        <Skeleton className="h-5 w-20 rounded-full bg-muted/40" />
                        <Skeleton className="h-5 w-24 rounded-full bg-muted/40" />
                    </div>
                    <Skeleton className="h-8 w-3/4 bg-muted/40" />
                    <Skeleton className="h-4 w-full bg-muted/40" />
                    <Skeleton className="h-4 w-5/6 bg-muted/40" />
                    <div className="flex items-center gap-4 pt-2">
                        <Skeleton className="h-6 w-24 rounded-full bg-muted/40" />
                        <Skeleton className="h-4 w-16 bg-muted/40" />
                        <Skeleton className="h-4 w-12 bg-muted/40" />
                    </div>
                </div>
            </div>
        );
    }

    if (variant === 'popular') {
        return (
            <div className="flex gap-3 p-4">
                <Skeleton className="size-5 shrink-0 rounded bg-muted/30" />
                <div className="min-w-0 flex-1 space-y-2">
                    <Skeleton className="h-3.5 w-16 bg-muted/30" />
                    <Skeleton className="h-4 w-full bg-muted/30" />
                    <Skeleton className="h-4 w-4/5 bg-muted/30" />
                    <div className="flex gap-2 pt-1">
                        <Skeleton className="h-4 w-12 rounded-xs bg-muted/30" />
                        <Skeleton className="h-4 w-20 bg-muted/30" />
                    </div>
                </div>
            </div>
        );
    }

    // Standard card
    return (
        <div className="flex h-full flex-col overflow-hidden rounded-2xl border border-border/60 bg-card">
            <div className="relative aspect-16/10">
                <Skeleton className="h-full w-full" />
            </div>
            <div className="flex flex-1 flex-col gap-3 p-4">
                <div className="flex gap-1.5">
                    <Skeleton className="h-4 w-16 rounded-full" />
                </div>
                <div className="space-y-2">
                    <Skeleton className="h-5 w-11/12" />
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-4/5" />
                </div>
                <div className="mt-auto flex items-center gap-2 pt-2">
                    <Skeleton className="h-4 w-12 rounded-xs" />
                    <Skeleton className="h-4 w-20" />
                    <Skeleton className="ml-auto h-4 w-16" />
                </div>
            </div>
        </div>
    );
}

export function BlogPopularPostsSkeleton() {
    return (
        <section className="overflow-hidden rounded-2xl border border-border/60 bg-card">
            <div className="flex items-center justify-between border-b border-border/60 px-5 py-3.5">
                <Skeleton className="h-4 w-32" />
            </div>
            <div className="relative aspect-video">
                <Skeleton className="h-full w-full" />
            </div>
            <div className="divide-y divide-border/50">
                {Array.from({ length: 4 }).map((_, idx) => (
                    <BlogPostCardSkeleton key={idx} variant="popular" />
                ))}
            </div>
        </section>
    );
}

export function BlogLabelsSidebarSkeleton() {
    return (
        <section className="rounded-2xl border border-border/60 bg-card overflow-hidden">
            <div className="border-b border-border/60 px-5 py-3.5">
                <Skeleton className="h-4 w-36" />
            </div>
            <div className="p-4 space-y-4">
                <div>
                    <Skeleton className="h-3 w-16 mb-2" />
                    <div className="grid grid-cols-2 gap-2">
                        {Array.from({ length: 4 }).map((_, idx) => (
                            <Skeleton key={idx} className="h-8 rounded-lg" />
                        ))}
                    </div>
                </div>
                <div>
                    <Skeleton className="h-3 w-12 mb-2" />
                    <div className="flex flex-wrap gap-1.5">
                        {Array.from({ length: 8 }).map((_, idx) => (
                            <Skeleton key={idx} className="h-6 w-16 rounded-full" />
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
