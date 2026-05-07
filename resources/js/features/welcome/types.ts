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

export type PaginatedBooks = {
    index: any;
    data: CatalogBook[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
};

export type WelcomeProps = {
    canRegister?: boolean;
    filters: {
        search: string;
    };
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
    }[];
    featuredBooks: CatalogBook[];
    books: PaginatedBooks;
};
