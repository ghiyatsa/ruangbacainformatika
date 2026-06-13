import { usePage } from '@inertiajs/react';
import { AnimatePresence, motion } from 'motion/react';
import BookCard from '@/features/books/components/BookCard';
import BookCardSkeleton from '@/features/books/components/BookCardSkeleton';
import { useIsMobile } from '@/hooks/use-mobile';
import EmptyCatalogState from './EmptyCatalogState';
import type { CatalogBook } from '@/features/welcome/types';
import type { Auth, LoanRequestCart } from '@/types';
import type { BookCollectionViewMode } from './BookCollectionViewToggle';

interface BookGridProps {
    books: CatalogBook[];
    viewMode: BookCollectionViewMode;
    skeletonCount?: number;
    isLoading?: boolean;
    emptyTitle?: string;
    emptyDescription?: string;
    keyPrefix?: string;
}

const GRID_CLASS =
    'grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-6';
const LIST_CLASS = 'grid grid-cols-1 gap-3 lg:grid-cols-2';

export default function BookGrid({
    books,
    viewMode,
    skeletonCount = 6,
    isLoading = false,
    emptyTitle,
    emptyDescription,
    keyPrefix = 'book',
}: BookGridProps) {
    const isMobile = useIsMobile();
    const { auth, loanRequestCart } = usePage<{
        auth: Auth;
        loanRequestCart: LoanRequestCart | null;
    }>().props;

    if (isLoading) {
        return (
            <div className={isMobile ? '' : 'animate-in duration-500 fade-in'}>
                {viewMode === 'grid' ? (
                    <div className={GRID_CLASS}>
                        {Array.from({ length: skeletonCount }).map((_, i) => (
                            <BookCardSkeleton key={i} />
                        ))}
                    </div>
                ) : (
                    <div className={LIST_CLASS}>
                        {Array.from({ length: skeletonCount }).map((_, i) => (
                            <BookCardSkeleton key={i} variant="compact" />
                        ))}
                    </div>
                )}
            </div>
        );
    }

    if (books.length === 0) {
        return (
            <EmptyCatalogState
                title={emptyTitle}
                description={emptyDescription}
            />
        );
    }

    const content =
        viewMode === 'grid' ? (
            <div className={GRID_CLASS}>
                {books.map((book, index) => (
                    <BookCard
                        key={book.id || `${keyPrefix}-grid-${index}`}
                        book={book}
                        auth={auth}
                        loanRequestCart={loanRequestCart}
                    />
                ))}
            </div>
        ) : (
            <div className={LIST_CLASS}>
                {books.map((book, index) => (
                    <BookCard
                        key={book.id || `${keyPrefix}-list-${index}`}
                        book={book}
                        variant="compact"
                        auth={auth}
                        loanRequestCart={loanRequestCart}
                    />
                ))}
            </div>
        );

    if (isMobile) {
        return content;
    }

    return (
        <AnimatePresence mode="wait">
            <motion.div
                key={viewMode}
                initial={{ opacity: 0, y: 6 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -6 }}
                transition={{ duration: 0.25 }}
            >
                {content}
            </motion.div>
        </AnimatePresence>
    );
}
