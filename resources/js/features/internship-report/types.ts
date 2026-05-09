export interface InternshipReportData {
    id: number;
    title: string;
    authorName: string;
    studentId: string;
    year: number | null;
    abstract: string | null;
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

export interface PaginatedInternshipReports {
    data: InternshipReportData[];
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

export interface InternshipReportCatalogPageProps {
    filters: InternshipReportFilters;
    years: number[];
    total: number;
    reports: PaginatedInternshipReports;
}
