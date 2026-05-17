import { Deferred, Link } from '@inertiajs/react';
import { ArrowRight, BookOpen } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import BookCard from '@/features/books/components/BookCard';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import type { CatalogBook, WelcomeProps } from '@/features/welcome/types';
import booksRoute from '@/routes/books';
import BookCollectionViewToggle from './BookCollectionViewToggle';
import type { BookCollectionViewMode } from './BookCollectionViewToggle';
import EmptyCatalogState from './EmptyCatalogState';
import SectionHeader from './SectionHeader';

interface NewBooksPreviewProps {
    books: WelcomeProps['books'];
    totalBooks: number;
}

export default function NewBooksPreview({
    books,
    totalBooks,
}: NewBooksPreviewProps) {
    const [viewMode, setViewMode] = useState<BookCollectionViewMode>('grid');
    const previewBooks = books?.data?.slice(0, 12) || [];

    return (
        <div className="flex flex-col gap-8 sm:gap-10">
            <SectionHeader
                title="Buku Terbaru"
                subtitle="Koleksi terbaru yang baru ditambahkan ke katalog."
                action={
                    <BookCollectionViewToggle
                        viewMode={viewMode}
                        onChange={setViewMode}
                    />
                }
            />

            <Deferred
                data="books"
                fallback={
                    <div className="animate-in duration-500 fade-in">
                        {viewMode === 'grid' ? (
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-6">
                                {Array.from({ length: 12 }).map((_, i) => (
                                    <BookCardSkeleton key={i} />
                                ))}
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                {Array.from({ length: 8 }).map((_, i) => (
                                    <BookCardSkeleton
                                        key={i}
                                        variant="compact"
                                    />
                                ))}
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
                                <div className="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-6">
                                    {previewBooks.map(
                                        (book: CatalogBook, index: number) => (
                                            <BookCard
                                                key={book.id || `grid-${index}`}
                                                book={book}
                                            />
                                        ),
                                    )}
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                    {previewBooks.map(
                                        (book: CatalogBook, index: number) => (
                                            <BookCard
                                                key={book.id || `list-${index}`}
                                                book={book}
                                                variant="compact"
                                            />
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

            <div className="flex flex-col items-center gap-2">
                <Button asChild size="lg" className="gap-2 rounded-xl px-8">
                    <Link href={booksRoute.index.url()}>
                        <BookOpen className="size-4" />
                        {totalBooks > 0
                            ? `Lihat ${totalBooks.toLocaleString('id-ID')}+ Buku`
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
    );
}
