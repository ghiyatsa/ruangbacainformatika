import { Deferred, Link } from '@inertiajs/react';
import { AutoSkeleton } from 'auto-skeleton-react';
import {
    ArrowRight,
    BookOpen,
    Calendar,
    ChevronLeft,
    ChevronRight,
    Eye,
    GraduationCap,
    LayoutGrid,
    LayoutList,
    Sparkles,
} from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useCallback, useEffect, useState } from 'react';
import BookController from '@/actions/App/Http/Controllers/BookController';
import BookCard from '@/components/catalog/BookCard';
import BookCardSkeleton from '@/components/catalog/BookCardSkeleton';
import BookListItem from '@/components/catalog/BookListItem';
import BookListItemSkeleton from '@/components/catalog/BookListItemSkeleton';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import booksRoute from '@/routes/books';
import type { CatalogBook, WelcomeProps } from './types';

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

    const count = featuredBooks?.length ?? 0;

    const goNext = useCallback(() => {
        if (count <= 1) {
            return;
        }

        setCurrentIndex((prev) => (prev + 1) % count);
    }, [count]);

    const goPrev = useCallback(() => {
        if (count <= 1) {
            return;
        }

        setCurrentIndex((prev) => (prev - 1 + count) % count);
    }, [count]);

    useEffect(() => {
        if (count <= 1 || isPaused) {
            return;
        }

        const interval = setInterval(goNext, 5000);

        return () => clearInterval(interval);
    }, [count, isPaused, goNext]);

    const book = featuredBooks?.[currentIndex] || null;

    return (
        <div className="relative overflow-hidden rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/[0.04] via-transparent to-primary/[0.02]">
            <Deferred
                data="featuredBooks"
                fallback={
                    <AutoSkeleton
                        loading={true}
                        config={{
                            animation: 'pulse',
                            borderRadius: 8,
                        }}
                    >
                        <div className="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:gap-8 sm:p-8">
                            <div className="mx-auto w-36 shrink-0 sm:mx-0 sm:w-40 md:w-44">
                                <div className="aspect-3/4 overflow-hidden rounded-xl border bg-background">
                                    <img
                                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1' height='1'%3E%3C/svg%3E"
                                        alt=""
                                        className="h-full w-full object-cover"
                                        data-skeleton-role="image"
                                    />
                                </div>
                            </div>
                            <div className="flex flex-1 flex-col gap-3 text-center sm:text-left">
                                <span className="text-xs font-bold uppercase">
                                    Koleksi Sorotan
                                </span>
                                <h3 className="text-lg font-bold sm:text-xl">
                                    Judul buku sorotan placeholder teks
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Deskripsi singkat buku sorotan yang sedang
                                    dimuat
                                </p>
                                <div className="flex gap-2">
                                    <span className="rounded-full border px-2.5 py-1 text-xs">
                                        2024
                                    </span>
                                    <span className="rounded-full border px-2.5 py-1 text-xs">
                                        Tersedia
                                    </span>
                                </div>
                            </div>
                        </div>
                    </AutoSkeleton>
                }
            >
                <AnimatePresence mode="wait">
                    {book ? (
                        <motion.div
                            key={book.id}
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
                                    <img
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
                                    {book.categories.slice(0, 2).map((c) => (
                                        <span
                                            key={c}
                                            className="rounded-full border bg-background px-2.5 py-1 text-xs font-medium text-muted-foreground"
                                        >
                                            {c}
                                        </span>
                                    ))}
                                    <span className="inline-flex items-center gap-1 rounded-full border bg-background px-2.5 py-1 text-xs font-medium text-muted-foreground">
                                        <Eye className="size-3" />
                                        {book.viewCount}
                                    </span>
                                </div>

                                {/* Author */}
                                <p className="text-xs text-muted-foreground">
                                    {book.authors.join(', ') ||
                                        'Penulis tidak tersedia'}
                                </p>
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

            {/* Navigation + pagination */}
            {count > 1 && (
                <div className="flex items-center justify-between border-t border-primary/10 px-5 py-3 sm:px-8">
                    <div className="flex items-center gap-1.5">
                        {featuredBooks?.map((_, i) => (
                            <button
                                key={i}
                                onClick={() => setCurrentIndex(i)}
                                className={`h-1.5 rounded-full transition-all duration-300 ${
                                    i === currentIndex
                                        ? 'w-6 bg-primary'
                                        : 'w-1.5 bg-primary/20 hover:bg-primary/40'
                                }`}
                                aria-label={`Buku sorotan ${i + 1}`}
                            />
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
 * Main Section
 * ─────────────────────────────────────────────────── */
export default function CatalogSection({
    stats,
    featuredBooks,
    books,
}: CatalogSectionProps) {
    const previewBooks = books?.data?.slice(0, 8) || [];
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

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
                                <Badge variant="secondary" className="w-fit">
                                    <GraduationCap className="mr-1.5 size-3.5" />
                                    Koleksi Akademik Terkurasi
                                </Badge>
                                <div>
                                    <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">
                                        Eksplorasi Katalog Digital
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Akses terbuka untuk penelusuran pustaka
                                        guna mendukung riset dan pembelajaran.
                                    </p>
                                </div>
                            </div>

                            {/* View mode toggle */}
                            <div className="flex items-center gap-1 self-start rounded-lg border bg-muted/50 p-1 sm:self-auto">
                                <Button
                                    variant={
                                        viewMode === 'grid'
                                            ? 'secondary'
                                            : 'ghost'
                                    }
                                    size="icon"
                                    className="size-8"
                                    onClick={() => setViewMode('grid')}
                                >
                                    <LayoutGrid className="size-4" />
                                    <span className="sr-only">Grid view</span>
                                </Button>
                                <Button
                                    variant={
                                        viewMode === 'list'
                                            ? 'secondary'
                                            : 'ghost'
                                    }
                                    size="icon"
                                    className="size-8"
                                    onClick={() => setViewMode('list')}
                                >
                                    <LayoutList className="size-4" />
                                    <span className="sr-only">List view</span>
                                </Button>
                            </div>
                        </div>

                        {/* Book grid / list */}
                        <Deferred
                            data="books"
                            fallback={
                                <div className="animate-in duration-500 fade-in">
                                    {viewMode === 'grid' ? (
                                        <div className="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
                                            {Array.from({ length: 8 }).map(
                                                (_, i) => (
                                                    <BookCardSkeleton key={i} />
                                                ),
                                            )}
                                        </div>
                                    ) : (
                                        <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                                            <div className="flex flex-col divide-y divide-border">
                                                {Array.from({ length: 6 }).map(
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
                            {previewBooks.length > 0 && (
                                <div className="animate-in duration-500 fade-in">
                                    {viewMode === 'grid' ? (
                                        <div className="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
                                            {previewBooks.map((book) => (
                                                <BookCard
                                                    key={book.id}
                                                    book={book}
                                                />
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                            {previewBooks.map((book) => (
                                                <div
                                                    key={book.id}
                                                    className="overflow-hidden rounded-xl border bg-card"
                                                >
                                                    <BookListItem book={book} />
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}
                        </Deferred>

                        {/* CTA */}
                        <div className="flex flex-col items-center gap-4 rounded-xl border bg-muted/30 p-6 sm:flex-row sm:justify-between sm:p-8">
                            <div className="text-center sm:text-left">
                                <p className="font-semibold">
                                    Jelajahi katalog lengkap
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {stats.booksCount} judul tersedia ·{' '}
                                    {stats.availableItemsCount} eksemplar siap
                                    dipinjam
                                </p>
                            </div>
                            <Button
                                asChild
                                size="lg"
                                className="gap-2 rounded-xl"
                            >
                                <Link href={booksRoute.index.url()}>
                                    <BookOpen className="size-4" />
                                    Lihat Semua Buku
                                    <ArrowRight className="size-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
