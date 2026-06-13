import type { PaginationData } from '@/types/pagination';

export interface AcademicWorkData {
    id: number;
    title: string;
    authorName: string;
    studentId: string;
    year: number | null;
    abstract: string | null;
    viewCount: number;
    keywords: string[];
}

export interface AcademicWorkShowProps {
    academicWork: {
        data: AcademicWorkData;
    };
    relatedWorks?: AcademicWorkData[];
}

export interface AcademicWorkFilters {
    search: string;
    year: number | null;
}

export type PaginatedAcademicWorks = PaginationData<AcademicWorkData>;

export interface AcademicWorkCatalogPageProps {
    workType: 'skripsi' | 'thesis';
    filters: AcademicWorkFilters;
    years: number[];
    total: number;
    academicWorks: PaginatedAcademicWorks;
}
