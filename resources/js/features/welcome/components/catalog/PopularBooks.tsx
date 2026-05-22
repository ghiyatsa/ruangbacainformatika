import { Deferred } from '@inertiajs/react';
import { AnimatePresence, motion } from 'motion/react';
import { useState } from 'react';
import BookCard from '@/features/books/components/BookCard';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import type { CatalogBook } from '@/features/welcome/types';
import BookCollectionViewToggle from './BookCollectionViewToggle';
import type { BookCollectionViewMode } from './BookCollectionViewToggle';
import EmptyCatalogState from './EmptyCatalogState';
import SectionHeader from './SectionHeader';

export default function PopularBooks({
    popularBooks,
}: {
    popularBooks: CatalogBook[] | undefined;
}) {
    const [viewMode, setViewMode] = useState<BookCollectionViewMode>('grid');
    const previewBooks = popularBooks?.slice(0, 6) || [];

    return (
        <div className="flex flex-col gap-8 sm:gap-10">
            <SectionHeader
                title="Buku Populer"
                subtitle="Koleksi yang paling sering dilihat."
                action={
                    <BookCollectionViewToggle
                        viewMode={viewMode}
                        onChange={setViewMode}
                    />
                }
            />

            <Deferred
                data="popularBooks"
                fallback={
                    <div className="animate-in duration-500 fade-in">
                        {viewMode === 'grid' ? (
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-6">
                                {Array.from({ length: 6 }).map((_, index) => (
                                    <BookCardSkeleton key={index} />
                                ))}
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                {Array.from({ length: 6 }).map((_, index) => (
                                    <BookCardSkeleton
                                        key={index}
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
                                    {previewBooks.map((book, index) => (
                                        <BookCard
                                            key={
                                                book.id ||
                                                `popular-grid-${index}`
                                            }
                                            book={book}
                                        />
                                    ))}
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                    {previewBooks.map((book, index) => (
                                        <BookCard
                                            key={
                                                book.id ||
                                                `popular-list-${index}`
                                            }
                                            book={book}
                                            variant="compact"
                                        />
                                    ))}
                                </div>
                            )}
                        </motion.div>
                    </AnimatePresence>
                ) : (
                    <EmptyCatalogState
                        title="Belum ada buku populer"
                        description="Data akan tampil di sini."
                    />
                )}
            </Deferred>
        </div>
    );
}
