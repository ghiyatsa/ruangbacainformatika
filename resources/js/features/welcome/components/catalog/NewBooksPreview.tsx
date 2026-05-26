import { Deferred, Link } from '@inertiajs/react';
import { ArrowRight, BookOpen } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import booksRoute from '@/routes/books';
import BookCollectionViewToggle from './BookCollectionViewToggle';
import BookGrid from './BookGrid';
import SectionHeader from './SectionHeader';
import type { WelcomeProps } from '@/features/welcome/types';
import type { BookCollectionViewMode } from './BookCollectionViewToggle';

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
                subtitle="Tambahan terbaru dalam katalog."
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
                    <BookGrid
                        books={[]}
                        viewMode={viewMode}
                        skeletonCount={12}
                        isLoading={true}
                    />
                }
            >
                <BookGrid
                    books={previewBooks}
                    viewMode={viewMode}
                    keyPrefix="new-books"
                />
            </Deferred>

            <div className="flex flex-col items-center gap-2">
                <Button asChild size="lg" className="gap-2 rounded-xl px-8">
                    <Link href={booksRoute.index.url()}>
                        <BookOpen className="size-4" />
                        {totalBooks > 0
                            ? `Lihat ${totalBooks.toLocaleString('id-ID')}+ judul`
                            : 'Lihat katalog buku'}
                        <ArrowRight className="size-4" />
                    </Link>
                </Button>
                {totalBooks > 0 && (
                    <p className="text-xs text-muted-foreground">
                        Menampilkan {previewBooks.length} dari{' '}
                        {totalBooks.toLocaleString('id-ID')} judul
                    </p>
                )}
            </div>
        </div>
    );
}
