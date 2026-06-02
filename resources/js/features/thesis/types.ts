import type { PaginationData } from '@/types/pagination';

export interface ThesisData {
    id: number;
    title: string;
    authorName: string;
    studentId: string;
    year: number | null;
    abstract: string | null;
    viewCount: number;
    keywords: string[];
}

export interface ThesisShowProps {
    thesis: {
        data: ThesisData;
    };
    relatedTheses?: ThesisData[];
}

export interface ThesisFilters {
    search: string;
    year: number | null;
}

export type PaginatedTheses = PaginationData<ThesisData>;

export interface ThesisCatalogPageProps {
    filters: ThesisFilters;
    years: number[];
    total: number;
    theses: PaginatedTheses;
}
