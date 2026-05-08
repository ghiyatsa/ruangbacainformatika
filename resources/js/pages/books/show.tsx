import BookDetailPage from '@/features/books/components/BookDetailPage';
import type { BookDetailPageProps } from '@/features/books/components/BookDetailPage';

export default function BookShow(props: BookDetailPageProps) {
    return <BookDetailPage {...props} />;
}

