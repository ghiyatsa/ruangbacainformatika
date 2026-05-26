import type { PaginationData } from '@/types/pagination';

export type CatalogBook = {
    id: number;
    title: string;
    subtitle: string | null;
    slug: string;
    isbn: string | null;
    description: string;
    shortDescription: string;
    coverImageUrl: string;
    authors?: string[];
    categories?: { name: string; slug: string }[];
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
};

export type PaginatedBooks = PaginationData<CatalogBook>;

export type WelcomeProps = {
    stats: {
        booksCount: number;
        featuredCount: number;
        availableItemsCount: number;
        searchResultsCount: number;
    };
    categories: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        booksCount: number;
    }[];
    featuredBooks: CatalogBook[];
    popularBooks: CatalogBook[];
    books: PaginatedBooks;
};
