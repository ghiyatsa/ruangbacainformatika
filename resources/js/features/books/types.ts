import type { PaginatedBooks } from '@/features/welcome/types';

export type ViewMode = 'grid' | 'list';

export interface BookCatalogStats {
    booksCount?: number;
    availableItemsCount: number;
    searchResultsCount: number;
}

export interface CategoryItem {
    id: number;
    name: string;
    slug: string;
    booksCount: number;
}

export interface BookCatalogPageProps {
    canRegister?: boolean;
    filters: { search: string; category: string };
    stats: BookCatalogStats;
    categories: CategoryItem[];
    books: PaginatedBooks;
}

export interface BookData {
    id: number;
    title: string;
    slug: string;
    isbn: string | null;
    issn: string | null;
    description: string;
    coverImageUrl: string;
    authors: string[];
    categories: { name: string; slug: string }[];
    publisher: string | null;
    publishedYear: number | null;
    pages: number | null;
    language: string;
    itemsCount: number;
    availableItemsCount: number;
    isFeatured: boolean;
    isBorrowable: boolean;
    isAvailable: boolean;
    viewCount: number;
}
