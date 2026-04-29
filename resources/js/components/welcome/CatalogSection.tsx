import { Deferred, Link } from '@inertiajs/react';
import { ArrowRight, BookOpen, Sparkles } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useEffect, useState } from 'react';
import BookCard from '@/components/catalog/BookCard';
import BookCardSkeleton from '@/components/catalog/BookCardSkeleton';
import BookListItem from '@/components/catalog/BookListItem';
import BookListItemSkeleton from '@/components/catalog/BookListItemSkeleton';
import CatalogHeader from '@/components/catalog/CatalogHeader';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
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

export default function CatalogSection({
    stats,
    featuredBooks,
    books,
}: CatalogSectionProps) {
    /** Show at most 4 books in the preview */
    const previewBooks = books?.data?.slice(0, 4) || [];

    const [currentIndex, setCurrentIndex] = useState(0);
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

    useEffect(() => {
        if (!featuredBooks || featuredBooks.length <= 1) {
            return;
        }

        const interval = setInterval(() => {
            setCurrentIndex((prev) => (prev + 1) % featuredBooks.length);
        }, 5000); // Change book every 5 seconds

        return () => clearInterval(interval);
    }, [featuredBooks]);

    const featuredBook = featuredBooks?.[currentIndex] || null;

    return (
        <section className="py-20 lg:py-28">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="flex flex-col gap-16 lg:flex-row lg:items-start">
                    {/* Left — spotlight / featured book */}
                    <aside className="sticky top-24 w-full lg:w-[360px] lg:shrink-0">
                        <Card className="overflow-hidden border-primary/20 bg-primary/5 shadow-inner">
                            <CardHeader className="pb-4">
                                <div className="flex items-center gap-2 text-primary">
                                    <Sparkles className="size-4" />
                                    <span className="text-xs font-bold tracking-widest uppercase">
                                        Koleksi Sorotan
                                    </span>
                                </div>
                                <CardTitle className="text-xl">
                                    Referensi Unggulan
                                </CardTitle>
                            </CardHeader>

                            <CardContent className="min-h-[400px] space-y-4">
                                <Deferred
                                    data="featuredBooks"
                                    fallback={
                                        <div className="animate-pulse space-y-4">
                                            <Skeleton className="aspect-3/4 w-full rounded-xl" />
                                            <div className="space-y-2">
                                                <Skeleton className="h-5 w-3/4" />
                                                <Skeleton className="h-4 w-full" />
                                                <Skeleton className="h-4 w-full" />
                                            </div>
                                            <div className="grid grid-cols-2 gap-2">
                                                <Skeleton className="h-10 w-full rounded-lg" />
                                                <Skeleton className="h-10 w-full rounded-lg" />
                                            </div>
                                        </div>
                                    }
                                >
                                    <AnimatePresence mode="wait">
                                        {featuredBook ? (
                                            <motion.div
                                                key={featuredBook.id}
                                                initial={{ x: 40, opacity: 0 }}
                                                animate={{ x: 0, opacity: 1 }}
                                                exit={{ x: -40, opacity: 0 }}
                                                transition={{
                                                    duration: 0.5,
                                                    ease: 'easeInOut',
                                                }}
                                                className="space-y-4"
                                            >
                                                <div className="aspect-3/4 overflow-hidden rounded-xl border bg-background shadow-lg">
                                                    <img
                                                        src={
                                                            featuredBook.coverImageUrl
                                                        }
                                                        alt={featuredBook.title}
                                                        className="h-full w-full object-cover"
                                                    />
                                                </div>
                                                <div className="space-y-2">
                                                    <h4 className="leading-tight font-bold">
                                                        {featuredBook.title}
                                                    </h4>
                                                    <p className="line-clamp-3 text-sm leading-relaxed text-muted-foreground">
                                                        {
                                                            featuredBook.shortDescription
                                                        }
                                                    </p>
                                                </div>
                                                <div className="grid grid-cols-2 gap-2 text-[10px]">
                                                    <div className="rounded-lg border bg-background p-2">
                                                        <span className="mb-0.5 block text-muted-foreground uppercase">
                                                            Tahun
                                                        </span>
                                                        <span className="font-bold">
                                                            {featuredBook.publishedYear ||
                                                                '—'}
                                                        </span>
                                                    </div>
                                                    <div className="rounded-lg border bg-background p-2">
                                                        <span className="mb-0.5 block text-muted-foreground uppercase">
                                                            Ketersediaan
                                                        </span>
                                                        <span className="font-bold text-primary">
                                                            {availabilityLabel(
                                                                featuredBook,
                                                            )}
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
                                                className="py-10 text-center text-muted-foreground"
                                            >
                                                Belum ada koleksi sorotan.
                                            </motion.div>
                                        )}
                                    </AnimatePresence>
                                </Deferred>
                            </CardContent>

                            <CardFooter className="pt-0 pb-6">
                                <Button
                                    variant="secondary"
                                    className="w-full"
                                    asChild
                                >
                                    <Link href={booksRoute.index.url()}>
                                        Jelajahi Semua Koleksi
                                        <ArrowRight className="ml-2 size-4" />
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    </aside>

                    {/* Right — book grid preview */}
                    <div className="flex flex-1 flex-col gap-10">
                        {/* Section heading */}
                        <CatalogHeader
                            title="Eksplorasi Katalog Digital"
                            badgeText="Koleksi Akademik Terkurasi"
                            description="Kami menyediakan akses terbuka untuk penelusuran pustaka guna mendukung riset dan pembelajaran teknik informatika."
                            viewMode={viewMode}
                            onViewModeChange={setViewMode}
                        />

                        {/* Preview book display */}
                        <Deferred
                            data="books"
                            fallback={
                                <div className="animate-in duration-500 fade-in">
                                    {viewMode === 'grid' ? (
                                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                            {Array.from({ length: 4 }).map(
                                                (_, i) => (
                                                    <BookCardSkeleton key={i} />
                                                ),
                                            )}
                                        </div>
                                    ) : (
                                        <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                                            <div className="flex flex-col divide-y divide-border">
                                                {Array.from({ length: 4 }).map(
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
                                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                            {previewBooks.map((book) => (
                                                <BookCard
                                                    key={book.id}
                                                    book={book}
                                                />
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                                            <div className="flex flex-col divide-y divide-border">
                                                {previewBooks.map((book) => (
                                                    <BookListItem
                                                        key={book.id}
                                                        book={book}
                                                    />
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}
                        </Deferred>

                        {/* CTA — "More Books" */}
                        <div className="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
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
                            <p className="text-sm text-muted-foreground">
                                {stats.booksCount} judul tersedia ·{' '}
                                {stats.availableItemsCount} eksemplar siap
                                dipinjam
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
