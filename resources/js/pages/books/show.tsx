import BookDetailPage from '@/features/catalog/components/BookDetailPage';
import type { BookDetailPageProps } from '@/features/catalog/components/BookDetailPage';

export default function BookShow(props: BookDetailPageProps) {
    return <BookDetailPage {...props} />;
}
