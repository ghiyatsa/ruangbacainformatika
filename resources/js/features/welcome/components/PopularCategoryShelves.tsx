import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import booksRoute from '@/routes/books';
import BookCollectionViewToggle from './BookCollectionViewToggle';
import BookGrid from './BookGrid';
import LazyDeferred from './LazyDeferred';
import SectionHeader from './SectionHeader';
import type { WelcomeProps } from '@/features/welcome/types';
import type { BookCollectionViewMode } from './BookCollectionViewToggle';

export default function PopularCategoryShelves({
    popularCategoryShelves,
}: {
    popularCategoryShelves: WelcomeProps['popularCategoryShelves'];
}) {
    const [viewMode, setViewMode] = useState<BookCollectionViewMode>('grid');
    const shelves = popularCategoryShelves ?? [];

    return (
        <LazyDeferred
            dataKey="popularCategoryShelves"
            isLoaded={!!popularCategoryShelves}
            fallback={
                <div className="w-full">
                    {Array.from({ length: 3 }).map((_, index) => (
                        <div
                            key={`popular-category-shelf-skeleton-${index}`}
                            className="w-full"
                        >
                            {index > 0 && (
                                <div className="w-full border-y border-border/60">
                                    <div
                                        className="mx-auto h-6 max-w-7xl px-4 sm:h-8 sm:px-6 lg:px-8"
                                        style={{
                                            backgroundImage:
                                                'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                                        }}
                                    />
                                </div>
                            )}
                            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">
                                <div className="flex flex-col gap-8 sm:gap-10">
                                    <SectionHeader
                                        title="Kategori populer"
                                        action={
                                            <BookCollectionViewToggle
                                                viewMode={viewMode}
                                                onChange={setViewMode}
                                            />
                                        }
                                    />

                                    <BookGrid
                                        books={[]}
                                        viewMode={viewMode}
                                        skeletonCount={6}
                                        isLoading={true}
                                    />
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            }
            rescueTitle="Daftar buku kategori populer belum tersedia"
            rescueDescription="Bagian ini dapat dimuat ulang tanpa memuat ulang seluruh halaman."
        >
            <div className="w-full">
                {shelves.map((shelf, index) => (
                    <div key={shelf.id} className="w-full">
                        {index > 0 && (
                            <div className="w-full border-y border-border/60">
                                <div
                                    className="mx-auto h-6 max-w-7xl px-4 sm:h-8 sm:px-6 lg:px-8"
                                    style={{
                                        backgroundImage:
                                            'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                                    }}
                                />
                            </div>
                        )}
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">
                            <div className="flex flex-col gap-8 sm:gap-10">
                                <SectionHeader
                                    title={shelf.name}
                                    action={
                                        <BookCollectionViewToggle
                                            viewMode={viewMode}
                                            onChange={setViewMode}
                                        />
                                    }
                                />

                                <BookGrid
                                    books={shelf.books}
                                    viewMode={viewMode}
                                    keyPrefix={`popular-category-${shelf.slug}`}
                                    emptyTitle={`Belum ada buku pada kategori ${shelf.name}`}
                                    emptyDescription="Daftar buku untuk kategori ini akan tampil di sini."
                                />

                                <div className="flex justify-center">
                                    <Button
                                        asChild
                                        size="lg"
                                        className="rounded-xl px-8"
                                    >
                                        <Link
                                            href={booksRoute.index.url({
                                                query: {
                                                    category: shelf.slug,
                                                },
                                            })}
                                        >
                                            Lihat semua buku {shelf.name}
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </LazyDeferred>
    );
}
