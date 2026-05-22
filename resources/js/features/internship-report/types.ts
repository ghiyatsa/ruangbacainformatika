import type { PaginationData } from '@/types/pagination';

export interface InternshipReportData {
    id: number;
    title: string;
    authorName: string;
    studentId: string;
    year: number | null;
    abstract: string | null;
    viewCount: number;
    keywords: string[];
}

export interface InternshipReportShowProps {
    report: {
        data: InternshipReportData;
    };
}

export interface InternshipReportFilters {
    search: string;
    year: number | null;
}

export type PaginatedInternshipReports = PaginationData<InternshipReportData>;

export interface InternshipReportCatalogPageProps {
    filters: InternshipReportFilters;
    years: number[];
    total: number;
    reports: PaginatedInternshipReports;
}
