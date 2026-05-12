import { Deferred, Link } from '@inertiajs/react';

import {
    ArrowRight,
    BookOpen,
    Calendar,
    ChevronLeft,
    ChevronRight,
    Eye,
    LayoutGrid,
    LayoutList,
    Library,
    Sparkles,
} from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import BookCard from '@/features/books/components/BookCard';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import BookListItem from '@/features/books/components/BookListItem';
import BookListItemSkeleton from '@/features/books/components/BookListItemSkeleton';
import type { CatalogBook, WelcomeProps } from '@/features/welcome/types';
import booksRoute from '@/routes/books';

interface CatalogSectionProps {
    stats: WelcomeProps['stats'];
    featuredBooks: WelcomeProps['featuredBooks'];
    books: WelcomeProps['books'];
}

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

const SLIDE_DURATION = 5000;

/* ───────────────────────────────────────────────────
 * Cover image with fallback on error
 * ─────────────────────────────────────────────────── */
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

/* ───────────────────────────────────────────────────
 * Featured Book Spotlight (horizontal carousel)
 * ─────────────────────────────────────────────────── */
function FeaturedSpotlight({
    featuredBooks,
}: {
    featuredBooks: CatalogBook[] | undefined;
}) {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isPaused, setIsPaused] = useState(false);
    const [progress, setProgress] = useState(0);
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);
    const progressRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const count = featuredBooks?.length ?? 0;

    const goTo = useCallback((index: number) => {
        setCurrentIndex(index);
        setProgress(0);
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

        // Progress tick every 50ms
        progressRef.current = setInterval(() => {
            setProgress((p) => Math.min(p + 50 / SLIDE_DURATION, 1));
        }, 50);

        intervalRef.current = setInterval(() => {
            setCurrentIndex((prev) => (prev + 1) % count);
            setProgress(0);
        }, SLIDE_DURATION);

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }

            if (progressRef.current) {
                clearInterval(progressRef.current);
            }
        };
    }, [count, isPaused, currentIndex]);

    const book = featuredBooks?.[currentIndex] || null;

    return (
        <div className="relative overflow-hidden rounded-2xl border border-primary/10 bg-background/40 backdrop-blur-sm dark:bg-background/10">
            {/* Gradient Overlay for premium look and background pattern integration */}
            <div className="absolute inset-0 -z-10 bg-linear-to-br from-primary/10 via-transparent to-primary/5 opacity-50 dark:from-primary/20 dark:to-transparent" />
            <Deferred
                data="featuredBooks"
                fallback={
                    <div className="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:gap-8 sm:p-8">
                        <div className="mx-auto w-36 shrink-0 sm:mx-0 sm:w-40 md:w-44">
                            <Skeleton className="aspect-3/4 w-full rounded-xl" />
                        </div>
                        <div className="flex flex-1 flex-col gap-3 text-center sm:text-left">
                            <Skeleton className="h-4 w-24" />
                            <Skeleton className="h-8 w-full" />
                            <div className="space-y-2">
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-5/6" />
                            </div>
                            <div className="flex gap-2">
                                <Skeleton className="h-6 w-16 rounded-full" />
                                <Skeleton className="h-6 w-16 rounded-full" />
                            </div>
                        </div>
                    </div>
                }
            >
                <AnimatePresence mode="wait">
                    {book ? (
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
                            {/* Cover image */}
                            <Link
                                href={BookController.show(book.slug)}
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

                            {/* Book details */}
                            <div className="flex flex-1 flex-col gap-3 text-center sm:text-left">
                                <div className="flex items-center justify-center gap-2 sm:justify-start">
                                    <Sparkles className="size-3.5 text-primary" />
                                    <span className="text-xs font-bold tracking-widest text-primary uppercase">
                                        Koleksi Sorotan
                                    </span>
                                </div>

                                {/* Author — prominent position */}
                                {Array.isArray(book.authors) &&
                                    book.authors.length > 0 && (
                                        <p className="text-xs font-medium text-primary/70">
                                            {book.authors.join(', ')}
                                        </p>
                                    )}

                                <Link
                                    href={BookController.show(book.slug)}
                                    className="group/title"
                                >
                                    <h3 className="text-lg leading-tight font-bold transition-colors group-hover/title:text-primary sm:text-xl md:text-2xl">
                                        {book.title}
                                    </h3>
                                </Link>

                                <p className="line-clamp-2 text-sm leading-relaxed text-muted-foreground sm:line-clamp-3">
                                    {book.shortDescription}
                                </p>

                                {/* Meta pills */}
                                <div className="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
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
                    ) : (
                        <motion.div
                            key="empty"
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            className="px-8 py-14 text-center text-muted-foreground"
                        >
                            Belum ada koleksi sorotan.
                        </motion.div>
                    )}
                </AnimatePresence>
            </Deferred>

            {/* Navigation + pagination with progress indicator */}
            {count > 1 && (
                <div className="flex items-center justify-between border-t border-primary/10 px-5 py-3 sm:px-8">
                    <div className="flex items-center gap-2">
                        {featuredBooks?.map((_, i) => (
                            <button
                                key={i}
                                onClick={() => goTo(i)}
                                className="group relative h-1.5 overflow-hidden rounded-full transition-all duration-300"
                                style={{
                                    width: i === currentIndex ? 28 : 6,
                                    backgroundColor:
                                        i === currentIndex
                                            ? 'hsl(var(--primary) / 0.2)'
                                            : 'hsl(var(--primary) / 0.2)',
                                }}
                                aria-label={`Buku sorotan ${i + 1}`}
                            >
                                {i === currentIndex ? (
                                    <motion.div
                                        className="absolute inset-y-0 left-0 rounded-full bg-primary"
                                        style={{ width: `${progress * 100}%` }}
                                    />
                                ) : (
                                    <div className="absolute inset-0 rounded-full bg-primary/20 transition-colors group-hover:bg-primary/40" />
                                )}
                            </button>
                        ))}
                    </div>
                    <div className="flex items-center gap-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-7 rounded-full"
                            onClick={goPrev}
                        >
                            <ChevronLeft className="size-4" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-7 rounded-full"
                            onClick={goNext}
                        >
                            <ChevronRight className="size-4" />
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
}

/* ───────────────────────────────────────────────────
 * Empty state
 * ─────────────────────────────────────────────────── */
function EmptyCatalogState() {
    return (
        <motion.div
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4 }}
            className="flex flex-col items-center gap-4 rounded-2xl border border-dashed bg-muted/30 px-6 py-16 text-center"
        >
            <div className="flex size-14 items-center justify-center rounded-full bg-muted">
                <Library className="size-7 text-muted-foreground" />
            </div>
            <div className="flex flex-col gap-1">
                <p className="font-semibold text-foreground">
                    Belum ada koleksi terbaru
                </p>
                <p className="text-sm text-muted-foreground">
                    Koleksi buku akan muncul di sini setelah ditambahkan ke
                    katalog.
                </p>
            </div>
        </motion.div>
    );
}

/* ───────────────────────────────────────────────────
 * Main Section
 * ─────────────────────────────────────────────────── */
export default function CatalogSection({
    stats,
    featuredBooks,
    books,
}: CatalogSectionProps) {
    const previewBooks = books?.data?.slice(0, 12) || [];
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const totalBooks = stats?.booksCount ?? 0;

    return (
        <section className="py-16 sm:py-20 lg:py-28">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-12 lg:gap-16">
                    {/* Featured spotlight — full width */}
                    <FeaturedSpotlight featuredBooks={featuredBooks} />

                    {/* Catalog preview */}
                    <div className="flex flex-col gap-8 sm:gap-10">
                        {/* Header */}
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div className="flex flex-col gap-2">
                                {/* Section badge */}
                                <div className="flex items-center gap-2">
                                    <BookOpen className="size-3.5 text-primary" />
                                    <span className="text-xs font-bold tracking-widest text-primary uppercase">
                                        Katalog
                                    </span>
                                </div>
                                <div>
                                    <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">
                                        Buku Terbaru
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Koleksi buku yang baru saja ditambahkan
                                        ke dalam katalog.
                                    </p>
                                </div>
                            </div>

                            {/* View mode toggle with tooltips */}
                            <TooltipProvider>
                                <div className="flex items-center gap-1 self-start rounded-lg border bg-muted/50 p-1 sm:self-auto">
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <Button
                                                variant={
                                                    viewMode === 'grid'
                                                        ? 'secondary'
                                                        : 'ghost'
                                                }
                                                size="icon"
                                                className="size-8"
                                                onClick={() =>
                                                    setViewMode('grid')
                                                }
                                                aria-label="Tampilan grid"
                                            >
                                                <LayoutGrid className="size-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            Tampilan Grid
                                        </TooltipContent>
                                    </Tooltip>
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <Button
                                                variant={
                                                    viewMode === 'list'
                                                        ? 'secondary'
                                                        : 'ghost'
                                                }
                                                size="icon"
                                                className="size-8"
                                                onClick={() =>
                                                    setViewMode('list')
                                                }
                                                aria-label="Tampilan daftar"
                                            >
                                                <LayoutList className="size-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            Tampilan Daftar
                                        </TooltipContent>
                                    </Tooltip>
                                </div>
                            </TooltipProvider>
                        </div>

                        {/* Book grid / list with AnimatePresence */}
                        <Deferred
                            data="books"
                            fallback={
                                <div className="animate-in duration-500 fade-in">
                                    {viewMode === 'grid' ? (
                                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                                            {Array.from({ length: 10 }).map(
                                                (_, i) => (
                                                    <BookCardSkeleton key={i} />
                                                ),
                                            )}
                                        </div>
                                    ) : (
                                        <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                                            <div className="flex flex-col divide-y divide-border">
                                                {Array.from({ length: 10 }).map(
                                                    (_, i) => (
                                                        <BookListItemSkeleton
                                                            key={i}
                                                        />
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            }
                        >
                            {previewBooks.length > 0 ? (
                                <AnimatePresence mode="wait">
                                    <motion.div
                                        key={viewMode}
                                        initial={{ opacity: 0, y: 6 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        exit={{ opacity: 0, y: -6 }}
                                        transition={{ duration: 0.25 }}
                                    >
                                        {viewMode === 'grid' ? (
                                            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                                                {previewBooks.map(
                                                    (
                                                        book: CatalogBook,
                                                        index: number,
                                                    ) => (
                                                        <BookCard
                                                            key={
                                                                book.id ||
                                                                `grid-${index}`
                                                            }
                                                            book={book}
                                                        />
                                                    ),
                                                )}
                                            </div>
                                        ) : (
                                            <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                                {previewBooks.map(
                                                    (
                                                        book: CatalogBook,
                                                        index: number,
                                                    ) => (
                                                        <div
                                                            key={
                                                                book.id ||
                                                                `list-${index}`
                                                            }
                                                            className="overflow-hidden rounded-xl border bg-card"
                                                        >
                                                            <BookListItem
                                                                book={book}
                                                            />
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        )}
                                    </motion.div>
                                </AnimatePresence>
                            ) : (
                                <EmptyCatalogState />
                            )}
                        </Deferred>

                        {/* CTA — centered with total count */}
                        <div className="flex flex-col items-center gap-2">
                            <Button
                                asChild
                                size="lg"
                                className="gap-2 rounded-xl px-8"
                            >
                                <Link href={booksRoute.index.url()}>
                                    <BookOpen className="size-4" />
                                    {totalBooks > 0
                                        ? `Lihat Semua ${totalBooks.toLocaleString('id-ID')}+ Buku`
                                        : 'Lihat Semua Buku'}
                                    <ArrowRight className="size-4" />
                                </Link>
                            </Button>
                            {totalBooks > 0 && (
                                <p className="text-xs text-muted-foreground">
                                    Menampilkan {previewBooks.length} dari{' '}
                                    {totalBooks.toLocaleString('id-ID')} koleksi
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
