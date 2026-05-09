export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginationData<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
    path: string;
    first_page_url: string;
    last_page_url: string;
};

export type PaginationMetaData = Omit<PaginationData<never>, 'data'>;
