import { Deferred, Link } from '@inertiajs/react';
import {
    BookOpen,
    Calendar,
    ChevronLeft,
    ChevronRight,
    Eye,
} from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { instantLoadingPageProps } from '@/lib/inertia-loading';
import DeferredCatalogRescue from './DeferredCatalogRescue';
import type { CatalogBook } from '@/features/welcome/types';

const SLIDE_DURATION = 5000;

function availabilityLabel(book: CatalogBook): string {
    if (!book.isAvailable) {
        return 'Kosong';
    }

    if (!book.isBorrowable) {
        return 'Referensi';
    }

    return 'Tersedia';
}

function availabilityColor(book: CatalogBook): string {
    if (!book.isAvailable) {
        return 'text-muted-foreground';
    }

    if (!book.isBorrowable) {
        return 'text-amber-600 dark:text-amber-400';
    }

    return 'text-emerald-600 dark:text-emerald-400';
}

function CoverImage({
    src,
    alt,
    className,
}: {
    src: string;
    alt: string;
    className?: string;
}) {
    const [errored, setErrored] = useState(false);

    if (errored) {
        return (
            <div
                className={`flex items-center justify-center bg-muted ${className ?? ''}`}
            >
                <BookOpen className="size-10 text-muted-foreground/40" />
            </div>
        );
    }

    return (
        <img
            src={src}
            alt={alt}
            className={className}
            onError={() => setErrored(true)}
        />
    );
}

function FeaturedSpotlightSkeleton() {
    return (
        <div className="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:gap-8 sm:p-8">
            <div className="mx-auto w-36 shrink-0 sm:mx-0 sm:w-40 md:w-44">
                <Skeleton className="aspect-3/4 w-full rounded-xl" />
            </div>

            <div className="flex flex-1 flex-col gap-3 text-center sm:text-left">
                <div className="flex min-h-4 justify-center sm:justify-start">
                    <Skeleton className="h-4 w-28" />
                </div>

                <div className="min-h-[3.5rem] space-y-2">
                    <Skeleton className="mx-auto h-7 w-full max-w-xl sm:mx-0" />
                    <Skeleton className="mx-auto h-7 w-4/5 max-w-lg sm:mx-0" />
                </div>

                <div className="min-h-[3rem] space-y-2">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-5/6" />
                    <Skeleton className="hidden h-4 w-2/3 sm:block" />
                </div>

                <div className="flex min-h-8 flex-wrap items-center justify-center gap-2 sm:justify-start">
                    <Skeleton className="h-6 w-18 rounded-full" />
                    <Skeleton className="h-6 w-22 rounded-full" />
                    <Skeleton className="h-6 w-20 rounded-full" />
                    <Skeleton className="h-6 w-14 rounded-full" />
                </div>
            </div>
        </div>
    );
}

export default function FeaturedSpotlight({
    featuredBooks,
}: {
    featuredBooks: CatalogBook[] | undefined;
}) {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isPaused, setIsPaused] = useState(false);
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const count = featuredBooks?.length ?? 0;

    const goTo = useCallback((index: number) => {
        setCurrentIndex(index);
    }, []);

    const goNext = useCallback(() => {
        if (count <= 1) {
            return;
        }

        goTo((currentIndex + 1) % count);
    }, [count, currentIndex, goTo]);

    const goPrev = useCallback(() => {
        if (count <= 1) {
            return;
        }

        goTo((currentIndex - 1 + count) % count);
    }, [count, currentIndex, goTo]);

    useEffect(() => {
        if (count <= 1 || isPaused) {
            return;
        }

        intervalRef.current = setInterval(() => {
            setCurrentIndex((prev) => (prev + 1) % count);
        }, SLIDE_DURATION);

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
        };
    }, [count, isPaused]);

    const book = featuredBooks?.[currentIndex] || null;

    return (
        <div className="relative overflow-hidden rounded-2xl border border-primary/10 bg-background/40 backdrop-blur-sm dark:bg-background/10">
            <div className="absolute inset-0 -z-10 bg-linear-to-br from-primary/10 via-transparent to-primary/5 opacity-50 dark:from-primary/20 dark:to-transparent" />
            <Deferred
                data="featuredBooks"
                fallback={<FeaturedSpotlightSkeleton />}
                rescue={({ reloading }) => (
                    <DeferredCatalogRescue
                        dataKey="featuredBooks"
                        title="Sorotan buku belum tersedia"
                        description="Bagian ini dapat dimuat ulang tanpa memuat ulang seluruh halaman."
                        reloading={reloading}
                    />
                )}
            >
                <AnimatePresence mode="wait">
                    {book && (
                        <motion.div
                            key={book.id || currentIndex}
                            initial={{ opacity: 0, y: 12 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -12 }}
                            transition={{ duration: 0.4, ease: 'easeInOut' }}
                            className="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:gap-8 sm:p-8"
                            onMouseEnter={() => setIsPaused(true)}
                            onMouseLeave={() => setIsPaused(false)}
                        >
                            <Link
                                href={BookController.show(book.slug)}
                                instant
                                component="books/show"
                                pageProps={instantLoadingPageProps()}
                                className="group/cover mx-auto w-36 shrink-0 sm:mx-0 sm:w-40 md:w-44"
                            >
                                <div className="aspect-3/4 overflow-hidden rounded-xl border bg-background shadow-lg ring-1 ring-black/5 transition-transform duration-300 group-hover/cover:scale-[1.03] dark:ring-white/5">
                                    <CoverImage
                                        src={book.coverImageUrl}
                                        alt={book.title}
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                            </Link>

                            <div className="flex flex-1 flex-col gap-3 text-center sm:text-left">
                                <div className="min-h-4">
                                    {Array.isArray(book.authors) &&
                                        book.authors.length > 0 && (
                                            <p className="text-xs font-medium text-primary/70">
                                                {book.authors.join(', ')}
                                            </p>
                                        )}
                                </div>

                                <Link
                                    href={BookController.show(book.slug)}
                                    instant
                                    component="books/show"
                                    pageProps={instantLoadingPageProps()}
                                    className="group/title"
                                >
                                    <div className="min-h-[3.5rem]">
                                        <h3 className="text-lg leading-tight font-bold transition-colors group-hover/title:text-primary sm:text-xl md:text-2xl">
                                            {book.title}
                                        </h3>
                                    </div>
                                </Link>

                                <div className="min-h-[3rem]">
                                    <p className="line-clamp-2 text-sm leading-relaxed text-muted-foreground sm:line-clamp-3">
                                        {book.shortDescription}
                                    </p>
                                </div>

                                <div className="flex min-h-8 flex-wrap items-center justify-center gap-2 sm:justify-start">
                                    {book.publishedYear && (
                                        <span className="inline-flex items-center gap-1 rounded-full border bg-background px-2.5 py-1 text-xs font-medium text-muted-foreground">
                                            <Calendar className="size-3" />
                                            {book.publishedYear}
                                        </span>
                                    )}
                                    <span
                                        className={`inline-flex items-center gap-1 rounded-full border bg-background px-2.5 py-1 text-xs font-semibold ${availabilityColor(book)}`}
                                    >
                                        <BookOpen className="size-3" />
                                        {availabilityLabel(book)}
                                    </span>
                                    {(Array.isArray(book.categories)
                                        ? book.categories
                                        : []
                                    )
                                        .slice(0, 2)
                                        .map((category, index) => (
                                            <span
                                                key={
                                                    category.slug ||
                                                    `cat-${index}`
                                                }
                                                className="rounded-full border bg-background px-2.5 py-1 text-xs font-medium text-muted-foreground"
                                            >
                                                {category.name}
                                            </span>
                                        ))}
                                    <span className="inline-flex items-center gap-1 rounded-full border bg-background px-2.5 py-1 text-xs font-medium text-muted-foreground">
                                        <Eye className="size-3" />
                                        {book.viewCount}
                                    </span>
                                </div>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </Deferred>

            {count > 1 && (
                <div className="flex items-center justify-between border-t border-primary/10 px-5 py-3 sm:px-8">
                    <div className="text-xs font-medium tabular-nums text-muted-foreground">
                        {currentIndex + 1}/{count}
                    </div>
                    <div className="flex items-center gap-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-11 rounded-full"
                            onClick={goPrev}
                            aria-label="Buku sorotan sebelumnya"
                            title="Buku sorotan sebelumnya"
                        >
                            <ChevronLeft className="size-4" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-11 rounded-full"
                            onClick={goNext}
                            aria-label="Buku sorotan berikutnya"
                            title="Buku sorotan berikutnya"
                        >
                            <ChevronRight className="size-4" />
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
}
