export interface SkripsiData {
    id: number;
    title: string;
    authorName: string;
    studentId: string;
    year: number | null;
    abstract: string | null;
    keywords: string[];
}

export interface SkripsiShowProps {
    skripsi: {
        data: SkripsiData;
    };
}

export interface SkripsiFilters {
    search: string;
    year: number | null;
}

export interface PaginatedSkripsis {
    data: SkripsiData[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
}

export interface SkripsiCatalogPageProps {
    filters: SkripsiFilters;
    years: number[];
    total: number;
    skripsis: PaginatedSkripsis;
}
