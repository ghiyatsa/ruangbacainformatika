import BookCatalogPage from '@/features/books/components/BookCatalogPage';
import type { BookCatalogPageProps } from '@/features/books/types';

export default function Catalog(props: BookCatalogPageProps) {
    return <BookCatalogPage {...props} />;
}
