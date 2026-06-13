import { useState } from 'react';
import BookCollectionViewToggle from './BookCollectionViewToggle';
import BookGrid from './BookGrid';
import LazyDeferred from './LazyDeferred';
import SectionHeader from './SectionHeader';
import type { CatalogBook } from '@/features/welcome/types';
import type { BookCollectionViewMode } from './BookCollectionViewToggle';

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
                subtitle="Buku yang paling sering dilihat."
                action={
                    <BookCollectionViewToggle
                        viewMode={viewMode}
                        onChange={setViewMode}
                    />
                }
            />

            <LazyDeferred
                dataKey="popularBooks"
                isLoaded={!!popularBooks}
                fallback={
                    <BookGrid
                        books={[]}
                        viewMode={viewMode}
                        skeletonCount={6}
                        isLoading={true}
                    />
                }
                rescueTitle="Daftar buku populer belum tersedia"
                rescueDescription="Bagian ini dapat dimuat ulang tanpa memuat ulang seluruh halaman."
            >
                <BookGrid
                    books={previewBooks}
                    viewMode={viewMode}
                    keyPrefix="popular-books"
                    emptyTitle="Belum ada data buku populer"
                    emptyDescription="Daftar buku yang paling sering dilihat akan tampil di sini."
                />
            </LazyDeferred>
        </div>
    );
}
