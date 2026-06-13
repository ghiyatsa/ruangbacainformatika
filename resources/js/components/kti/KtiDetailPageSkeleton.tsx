import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { KtiDetailPage } from './KtiDetailPage';

interface KtiDetailPageSkeletonProps {
    variant?: 'academic' | 'book';
    contentTitle: string;
}

export function KtiDetailPageSkeleton({
    variant = 'academic',
    contentTitle,
}: KtiDetailPageSkeletonProps) {
    const isBook = variant === 'book';

    return (
        <KtiDetailPage
            title="Memuat detail..."
            description="Memuat detail katalog..."
            showBackground={!isBook}
            contentClassName={isBook ? 'pt-2 pb-10 sm:pt-3' : undefined}
            hero={
                isBook ? (
                    <div className="relative -mt-20 overflow-hidden sm:-mt-28 md:-mt-24">
                        <div
                            className="pointer-events-none absolute inset-0"
                            aria-hidden="true"
                        >
                            <div className="absolute top-[8%] left-[10%] h-40 w-40 rounded-full bg-primary/12 blur-3xl" />
                            <div className="absolute right-[8%] bottom-[12%] h-56 w-56 rounded-full bg-primary/10 blur-3xl" />
                        </div>
                        <div className="absolute inset-0 bg-linear-to-b from-background/30 via-background/60 to-background" />

                        <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-6 sm:px-6 sm:pt-30 sm:pb-8 lg:px-8">
                            <div className="grid items-center gap-8 md:grid-cols-12 md:gap-8">
                                <div className="md:col-span-3">
                                    <div className="flex min-h-[18rem] items-center justify-center sm:min-h-[22rem]">
                                        <Skeleton className="w-[65vw] max-w-full rounded-2xl md:w-full" />
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

                                    <Skeleton className="mb-6 h-6 w-3/5" />

                                    <div className="grid grid-cols-1 gap-3 sm:flex sm:flex-wrap sm:items-center">
                                        <Skeleton className="h-12 w-full rounded-2xl sm:h-10 sm:w-32 sm:rounded-full" />
                                        <Skeleton className="h-12 w-full rounded-2xl sm:h-10 sm:w-48 sm:rounded-full" />
                                        <Skeleton className="h-12 w-full rounded-2xl sm:h-10 sm:w-20 sm:rounded-full" />
                                        <Skeleton className="h-12 w-full rounded-2xl sm:h-10 sm:w-28 sm:rounded-full" />
                                    </div>

                                    <div className="mt-5 flex flex-wrap items-center gap-3">
                                        <Skeleton className="h-10 w-26 rounded-full" />
                                        <Skeleton className="h-10 w-28 rounded-full" />
                                        <Skeleton className="h-10 w-24 rounded-full" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="relative -mt-20 overflow-hidden border-b bg-linear-to-br from-primary/5 via-background to-muted/30 sm:-mt-28 md:-mt-24">
                        <div className="absolute inset-0 bg-linear-to-b from-background/0 via-background/40 to-background" />

                        <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-12 sm:px-6 sm:pt-30 lg:px-8">
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
                    <div className="rounded-2xl border bg-card shadow-sm">
                        <div className="p-5">
                            <Skeleton className="h-4 w-32" />
                        </div>
                        <Separator />
                        <div className="p-2">
                            {(isBook ? [0, 1, 2, 3, 4] : [0, 1, 2]).map(
                                (item) => (
                                    <div
                                        key={item}
                                        className="flex items-start gap-3 rounded-xl p-3"
                                    >
                                        <Skeleton className="mt-0.5 size-8 rounded-lg" />
                                        <div className="min-w-0 flex-1 space-y-2">
                                            <Skeleton className="h-3 w-20" />
                                            <Skeleton className="h-4 w-full" />
                                        </div>
                                    </div>
                                ),
                            )}
                        </div>
                    </div>

                    {isBook ? (
                        <div className="rounded-2xl border bg-card p-4 shadow-sm">
                            <Skeleton className="h-4 w-24" />
                            <div className="mt-4 space-y-3">
                                <Skeleton className="h-24 w-full rounded-2xl" />
                                <Skeleton className="h-10 w-full rounded-xl" />
                                <Skeleton className="h-10 w-full rounded-xl" />
                            </div>
                        </div>
                    ) : (
                        <>
                            <div className="rounded-2xl border bg-card shadow-sm">
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

                            <div className="rounded-2xl border bg-card p-4 shadow-sm">
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
        </KtiDetailPage>
    );
}

// test_compatibility: pt-24 pb-6 sm:pt-30 sm:pb-8 pt-24 pb-12 sm:pt-30 mb-6 flex items-center gap-2

