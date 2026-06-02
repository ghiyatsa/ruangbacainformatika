import type { PaginationData } from '@/types/pagination';

export type CatalogBook = {
    id: number;
    title: string;
    slug: string;
    shortDescription: string;
    coverImageUrl: string;
    authors?: string[];
    categories?: { name: string; slug: string }[];
    publishedYear: number | null;
    pages: number | null;
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
        activeCategoriesCount: number;
        searchResultsCount: number;
    };
    categories: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        booksCount: number;
    }[];
    marqueeCategories?: {
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
