import type { CatalogBook, PaginatedBooks } from '@/features/books/types';

export type { CatalogBook, PaginatedBooks };

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
