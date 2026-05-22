import type { PaginationData } from '@/types/pagination';

export interface SkripsiData {
    id: number;
    title: string;
    authorName: string;
    studentId: string;
    year: number | null;
    abstract: string | null;
    viewCount: number;
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

export type PaginatedSkripsis = PaginationData<SkripsiData>;

export interface SkripsiCatalogPageProps {
    filters: SkripsiFilters;
    years: number[];
    total: number;
    skripsis: PaginatedSkripsis;
}
