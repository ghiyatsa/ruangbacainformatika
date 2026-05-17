import { Library } from 'lucide-react';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import BookCard from '@/features/books/components/BookCard';
import type { ViewMode } from '@/features/books/types';
import type { PaginatedBooks } from '@/features/welcome/types';

interface BookCatalogResultsProps {
    books: PaginatedBooks;
    viewMode: ViewMode;
}

export function BookCatalogResults({
    books,
    viewMode,
}: BookCatalogResultsProps) {
    if (!books) {
        return null;
    }

    return (
        <div className="flex flex-col gap-6">
            {books.data.length > 0 ? (
                viewMode === 'list' ? (
                    <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                        {books.data.map((book) => (
                            <BookCard
                                key={book.id}
                                book={book}
                                variant="compact"
                            />
                        ))}
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-6">
                        {books.data.map((book) => (
                            <BookCard key={book.id} book={book} />
                        ))}
                    </div>
                )
            ) : (
                <Empty className="border-2 py-20">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <Library />
                        </EmptyMedia>
                        <EmptyTitle>Buku tidak ditemukan</EmptyTitle>
                        <EmptyDescription>
                            Coba kata kunci lain atau hapus filter yang aktif.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>
            )}
        </div>
    );
}
