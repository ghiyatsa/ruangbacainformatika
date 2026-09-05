export interface QuickResultItem {
    type: 'book' | 'skripsi' | 'post';
    id: number;
    title: string;
    subtitle: string;
    url: string;
}

export interface SuggestionApiResponse {
    suggestions?: string[];
    quickResults?: {
        books?: QuickResultItem[];
        skripsi?: QuickResultItem[];
        posts?: QuickResultItem[];
    };
}

export interface HistoryItem {
    id: string;
    title: string;
    subtitle?: string;
    url: string;
    type: 'book' | 'skripsi' | 'post' | 'general';
    timestamp: number;
}
