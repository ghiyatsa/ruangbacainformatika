import { Deferred } from '@inertiajs/react';
import { useState } from 'react';
import BookCollectionViewToggle from './BookCollectionViewToggle';
import BookGrid from './BookGrid';
import DeferredCatalogRescue from './DeferredCatalogRescue';
import SectionHeader from './SectionHeader';
import type { CatalogBook } from '@/features/welcome/types';
import type { BookCollectionViewMode } from './BookCollectionViewToggle';

export default function MostBorrowedBooks({
    mostBorrowedBooks,
}: {
    mostBorrowedBooks: CatalogBook[] | undefined;
}) {
    const [viewMode, setViewMode] = useState<BookCollectionViewMode>('grid');
    const previewBooks = mostBorrowedBooks?.slice(0, 6) || [];

    return (
        <div className="flex flex-col gap-8 sm:gap-10">
            <SectionHeader
                title="Paling Sering Dipinjam"
                subtitle="Pilihan buku yang paling sering dipinjam."
                action={
                    <BookCollectionViewToggle
                        viewMode={viewMode}
                        onChange={setViewMode}
                    />
                }
            />

            <Deferred
                data="mostBorrowedBooks"
                fallback={
                    <BookGrid
                        books={[]}
                        viewMode={viewMode}
                        skeletonCount={6}
                        isLoading={true}
                    />
                }
                rescue={({ reloading }) => (
                    <DeferredCatalogRescue
                        dataKey="mostBorrowedBooks"
                        title="Daftar buku yang paling sering dipinjam belum tersedia"
                        description="Bagian ini dapat dimuat ulang tanpa memuat ulang seluruh halaman."
                        reloading={reloading}
                    />
                )}
            >
                <BookGrid
                    books={previewBooks}
                    viewMode={viewMode}
                    keyPrefix="most-borrowed-books"
                    emptyTitle="Belum ada riwayat peminjaman"
                    emptyDescription="Buku yang paling sering dipinjam akan tampil di sini."
                />
            </Deferred>
        </div>
    );
}
