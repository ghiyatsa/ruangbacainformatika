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

interface NewBooksPreviewProps {
    books: WelcomeProps['books'];
}

export default function NewBooksPreview({ books }: NewBooksPreviewProps) {
    const [viewMode, setViewMode] = useState<BookCollectionViewMode>('grid');
    const previewBooks = books?.data?.slice(0, 12) || [];

    return (
        <div className="flex flex-col gap-8 sm:gap-10">
            <SectionHeader
                title="Buku Terbaru"
                action={
                    <BookCollectionViewToggle
                        viewMode={viewMode}
                        onChange={setViewMode}
                    />
                }
            />

            <LazyDeferred
                dataKey="books"
                isLoaded={!!books}
                fallback={
                    <BookGrid
                        books={[]}
                        viewMode={viewMode}
                        skeletonCount={12}
                        isLoading={true}
                    />
                }
                rescueTitle="Daftar buku terbaru belum tersedia"
            >
                <BookGrid
                    books={previewBooks}
                    viewMode={viewMode}
                    keyPrefix="new-books"
                />
            </LazyDeferred>

            <div className="flex justify-center">
                <Button asChild size="lg" className="rounded-xl px-8">
                    <Link href={booksRoute.index.url()}>
                        Buku terbaru lainnya
                    </Link>
                </Button>
            </div>
        </div>
    );
}
