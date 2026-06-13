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
    popularCategoryShelves?: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        booksCount: number;
        books: CatalogBook[];
    }[];
    featuredBooks: CatalogBook[];
    popularBooks: CatalogBook[];
    mostBorrowedBooks: CatalogBook[];
    books: PaginatedBooks;
};
