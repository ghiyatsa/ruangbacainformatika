import { Link } from '@inertiajs/react';
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
import { formatViewCount } from '@/lib/utils';
import LazyDeferred from './LazyDeferred';
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
        return 'text-amber-700 dark:text-amber-400';
    }

    return 'text-emerald-700 dark:text-emerald-400';
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
                className={`relative flex flex-col items-center justify-center overflow-hidden bg-muted ${className ?? ''}`}
            >
                <div className="absolute inset-y-0 left-0 w-[3px] rounded-r-full bg-border" />
                <div className="flex flex-col items-center gap-2 px-4">
                    <BookOpen className="size-5 text-muted-foreground/25" />
                    <div className="flex flex-col items-center gap-1">
                        <div className="h-px w-10 rounded-full bg-muted-foreground/15" />
                        <div className="h-px w-7 rounded-full bg-muted-foreground/10" />
                    </div>
                </div>
            </div>
        );
    }

    return (
        <img
            src={src}
            alt={alt}
            width={176}
            height={234}
            className={className}
            onError={() => setErrored(true)}
        />
    );
}

function FeaturedSpotlightSkeleton() {
    return (
        <div className="flex flex-col">
            <div className="flex flex-col gap-5 py-5 px-4 sm:px-6 lg:px-8 sm:flex-row sm:items-center sm:gap-8 sm:py-8">
                <div className="mx-auto w-48 shrink-0 sm:mx-0 sm:w-40 md:w-44">
                    <div className="aspect-3/4 overflow-hidden rounded-xl bg-background ring-1 ring-black/5 dark:ring-white/5">
                        <Skeleton className="h-full w-full rounded-none" />
                    </div>
                </div>

                <div className="flex flex-1 flex-col gap-3 text-center sm:text-left">
                    {/* Authors */}
                    <div className="flex min-h-4 justify-center sm:justify-start">
                        <Skeleton className="h-3 w-28" />
                    </div>

                    {/* Title */}
                    <div className="flex min-h-[3.5rem] flex-col justify-center gap-1.5">
                        <Skeleton className="mx-auto h-5 w-11/12 max-w-xl sm:mx-0 sm:h-6" />
                        <Skeleton className="mx-auto h-5 w-3/4 max-w-md sm:mx-0 sm:h-6" />
                    </div>

                    {/* Short Description */}
                    <div className="flex min-h-[3rem] flex-col justify-center gap-1.5">
                        <Skeleton className="h-3 w-full" />
                        <Skeleton className="h-3 w-11/12" />
                        <Skeleton className="hidden h-3 w-4/5 sm:block" />
                    </div>

                    {/* Badges */}
                    <div className="flex min-h-8 flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <Skeleton className="h-6 w-16 rounded-full" />
                        <Skeleton className="h-6 w-20 rounded-full" />
                        <Skeleton className="h-6 w-24 rounded-full" />
                        <Skeleton className="h-6 w-14 rounded-full" />
                    </div>
                </div>
            </div>

            {/* Navigation Footer placeholder to prevent Cumulative Layout Shift (CLS) */}
            <div className="flex items-center justify-between border-t border-border/60 py-3 px-4 sm:px-6 lg:px-8">
                <Skeleton className="h-4 w-8" />
                <div className="flex items-center gap-1">
                    <Skeleton className="size-11 rounded-full" />
                    <Skeleton className="size-11 rounded-full" />
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

    if (featuredBooks !== undefined && count === 0) {
        return null;
    }

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
        <div className="relative overflow-hidden">
            <LazyDeferred
                dataKey="featuredBooks"
                isLoaded={!!featuredBooks}
                fallback={<FeaturedSpotlightSkeleton />}
                rescueTitle="Sorotan buku belum tersedia"
                rescueDescription="Bagian ini dapat dimuat ulang tanpa memuat ulang seluruh halaman."
            >
                <AnimatePresence mode="wait">
                    {book && (
                        <motion.div
                            key={book.id || currentIndex}
                            initial={{ opacity: 0, y: 12 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -12 }}
                            transition={{ duration: 0.4, ease: 'easeInOut' }}
                            className="flex flex-col gap-5 py-5 px-4 sm:px-6 lg:px-8 sm:flex-row sm:items-center sm:gap-8 sm:py-8"
                            onMouseEnter={() => setIsPaused(true)}
                            onMouseLeave={() => setIsPaused(false)}
                        >
                            <Link
                                href={BookController.show(book.slug)}
                                instant
                                component="books/show"
                                pageProps={instantLoadingPageProps()}
                                className="group/cover mx-auto w-48 shrink-0 sm:mx-0 sm:w-40 md:w-44"
                            >
                                <div className="aspect-3/4 overflow-hidden rounded-xl bg-background ring-1 ring-black/5 transition-transform duration-300 group-hover/cover:scale-[1.015] dark:ring-white/5">
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
                                            <p className="text-xs font-medium text-primary">
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
                                        <span className="inline-flex items-center gap-1 rounded-full bg-secondary px-2.5 py-1 text-xs font-medium text-secondary-foreground">
                                            <Calendar className="size-3" />
                                            {book.publishedYear}
                                        </span>
                                    )}
                                    <span
                                        className={`inline-flex items-center gap-1 rounded-full bg-secondary px-2.5 py-1 text-xs font-semibold ${availabilityColor(book)}`}
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
                                                className="rounded-full bg-secondary px-2.5 py-1 text-xs font-medium text-secondary-foreground"
                                            >
                                                {category.name}
                                            </span>
                                        ))}
                                    <span className="inline-flex items-center gap-1 rounded-full bg-secondary px-2.5 py-1 text-xs font-medium text-secondary-foreground">
                                        <Eye className="size-3" />
                                        {formatViewCount(book.viewCount)}
                                    </span>
                                </div>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </LazyDeferred>

            {count > 1 && (
                <div className="flex items-center justify-between border-t border-border/60 py-3 px-4 sm:px-6 lg:px-8">
                    <div className="text-xs font-medium text-muted-foreground tabular-nums">
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
