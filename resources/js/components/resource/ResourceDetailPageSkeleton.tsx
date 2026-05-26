import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { ResourceDetailPage } from './ResourceDetailPage';

interface ResourceDetailPageSkeletonProps {
    variant?: 'academic' | 'book';
    contentTitle: string;
}

export function ResourceDetailPageSkeleton({
    variant = 'academic',
    contentTitle,
}: ResourceDetailPageSkeletonProps) {
    const isBook = variant === 'book';

    return (
        <ResourceDetailPage
            title="Memuat detail..."
            description="Memuat detail katalog..."
            hero={
                isBook ? (
                    <div className="relative -mt-20 overflow-hidden sm:-mt-28">
                        <div className="absolute inset-0 bg-linear-to-b from-background/30 via-background/60 to-background" />

                        <div className="relative mx-auto max-w-7xl px-6 pt-32 pb-12 sm:pt-40 lg:px-8">
                            <div className="mb-8 flex items-center gap-2">
                                <Skeleton className="h-4 w-14" />
                                <Skeleton className="h-3 w-3" />
                                <Skeleton className="h-4 w-20" />
                                <Skeleton className="h-3 w-3" />
                                <Skeleton className="h-4 w-32" />
                            </div>

                            <div className="grid items-center gap-10 md:grid-cols-12 md:gap-12">
                                <div className="md:col-span-3">
                                    <div className="overflow-hidden rounded-2xl border border-white/10">
                                        <Skeleton className="aspect-3/4 w-full rounded-none" />
                                    </div>
                                </div>

                                <div className="flex flex-col justify-center md:col-span-9">
                                    <div className="mb-3 flex flex-wrap gap-1.5">
                                        <Skeleton className="h-6 w-20 rounded-full" />
                                        <Skeleton className="h-6 w-24 rounded-full" />
                                        <Skeleton className="h-6 w-18 rounded-full" />
                                    </div>

                                    <div className="mb-3 space-y-3">
                                        <Skeleton className="h-10 w-full max-w-4xl" />
                                        <Skeleton className="h-10 w-4/5 max-w-3xl" />
                                    </div>

                                    <div className="mb-3 space-y-2">
                                        <Skeleton className="h-5 w-2/5" />
                                        <Skeleton className="h-5 w-1/3" />
                                    </div>

                                    <Skeleton className="mb-6 h-6 w-3/5" />

                                    <div className="flex flex-wrap items-center gap-3">
                                        <Skeleton className="h-10 w-34 rounded-full" />
                                        <Skeleton className="h-10 w-48 rounded-full" />
                                        <Skeleton className="h-10 w-24 rounded-full" />
                                        <Skeleton className="h-10 w-28 rounded-full" />
                                        <Skeleton className="h-10 w-28 rounded-full" />
                                        <Skeleton className="h-10 w-24 rounded-full" />
                                    </div>

                                    <div className="mt-4 space-y-2">
                                        <Skeleton className="h-4 w-full max-w-2xl" />
                                        <Skeleton className="h-4 w-4/5 max-w-xl" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="relative -mt-20 overflow-hidden border-b bg-linear-to-br from-primary/5 via-background to-muted/30 sm:-mt-28">
                        <div className="absolute inset-0 bg-linear-to-b from-background/0 via-background/40 to-background" />

                        <div className="relative mx-auto max-w-7xl px-6 pt-32 pb-12 sm:pt-40 lg:px-8">
                            <div className="mb-8 flex items-center gap-2">
                                <Skeleton className="h-4 w-14" />
                                <Skeleton className="h-3 w-3" />
                                <Skeleton className="h-4 w-18" />
                                <Skeleton className="h-3 w-3" />
                                <Skeleton className="h-4 w-20" />
                            </div>

                            <div className="flex flex-col gap-6 md:flex-row md:items-start md:gap-10">
                                <div className="flex flex-1 flex-col justify-center">
                                    <div className="mb-3 space-y-3">
                                        <Skeleton className="h-8 w-full max-w-3xl" />
                                        <Skeleton className="h-8 w-4/5 max-w-2xl" />
                                    </div>

                                    <div className="flex flex-wrap items-center gap-3">
                                        <Skeleton className="h-4 w-34" />
                                        <Skeleton className="h-3 w-3 rounded-full" />
                                        <Skeleton className="h-4 w-28" />
                                        <Skeleton className="h-3 w-3 rounded-full" />
                                        <Skeleton className="h-4 w-14" />
                                        <Skeleton className="h-3 w-3 rounded-full" />
                                        <Skeleton className="h-4 w-12" />
                                    </div>

                                    <div className="mt-5 flex flex-wrap items-center gap-3">
                                        <Skeleton className="h-10 w-28 rounded-full" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )
            }
            sidebar={
                <div className="space-y-4">
                    <div className="rounded-2xl border bg-card/80 shadow-sm backdrop-blur-sm">
                        <div className="p-5">
                            <Skeleton className="h-4 w-32" />
                        </div>
                        <Separator />
                        <div className="space-y-3 p-4">
                            {(isBook ? [0, 1, 2, 3, 4] : [0, 1, 2]).map(
                                (item) => (
                                    <div key={item} className="space-y-2">
                                        <Skeleton className="h-3 w-20" />
                                        <Skeleton className="h-4 w-full" />
                                    </div>
                                ),
                            )}
                        </div>
                    </div>

                    {isBook ? (
                        <div className="rounded-2xl border bg-card/80 p-4 shadow-sm backdrop-blur-sm">
                            <Skeleton className="h-4 w-24" />
                            <div className="mt-4 space-y-3">
                                <Skeleton className="h-10 w-full rounded-xl" />
                                <Skeleton className="h-24 w-full rounded-2xl" />
                                <Skeleton className="h-10 w-full rounded-xl" />
                            </div>
                        </div>
                    ) : (
                        <>
                            <div className="rounded-2xl border bg-card/80 shadow-sm backdrop-blur-sm">
                                <div className="p-5">
                                    <Skeleton className="h-4 w-24" />
                                </div>
                                <Separator />
                                <div className="flex flex-wrap gap-2 p-4">
                                    <Skeleton className="h-6 w-16 rounded-full" />
                                    <Skeleton className="h-6 w-20 rounded-full" />
                                    <Skeleton className="h-6 w-14 rounded-full" />
                                    <Skeleton className="h-6 w-18 rounded-full" />
                                </div>
                            </div>

                            <div className="rounded-2xl border bg-card/80 p-4 shadow-sm backdrop-blur-sm">
                                <Skeleton className="h-4 w-28" />
                                <div className="mt-4 space-y-3">
                                    <Skeleton className="h-10 w-full rounded-xl" />
                                    <Skeleton className="h-10 w-full rounded-xl" />
                                </div>
                            </div>
                        </>
                    )}
                </div>
            }
        >
            <section>
                <div className="mb-5 flex items-center gap-3">
                    <Skeleton className="size-9 rounded-xl" />
                    <Skeleton className="h-7 w-28" />
                </div>

                <div className="space-y-4">
                    <div className="sr-only">{contentTitle}</div>
                    <div className="space-y-3">
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-11/12" />
                        <Skeleton className="h-4 w-10/12" />
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-4/5" />
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-5/6" />
                    </div>
                </div>
            </section>
        </ResourceDetailPage>
    );
}
