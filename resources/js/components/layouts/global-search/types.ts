export interface SearchResult {
    id: number;
    title: string;
    slug: string;
    coverImageUrl?: string;
    authors?: string[];
    authorName?: string;
    studentId?: string;
}

export interface SearchResponse {
    books: SearchResult[];
    skripsis: SearchResult[];
    internshipReports: SearchResult[];
}

export type SearchItemType = 'book' | 'skripsi' | 'internship_report';

export type SearchListItem = SearchResult & {
    itemType: SearchItemType;
};
