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

export interface BookCatalogFilters {
    search: string;
    category: string;
    year: number | null;
    featured: boolean;
    availability: boolean;
}

export interface BookCatalogPageProps {
    canRegister?: boolean;
    filters: BookCatalogFilters;
    stats: BookCatalogStats;
    categories: CategoryItem[];
    years: number[];
    books: PaginatedBooks;
}

export interface BookData {
    id: number;
    title: string;
    subtitle: string | null;
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
    language: string | null;
    itemsCount: number;
    availableItemsCount: number;
    isFeatured: boolean;
    isBorrowable: boolean;
    isAvailable: boolean;
    viewCount: number;
}
