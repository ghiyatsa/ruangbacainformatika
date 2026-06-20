import type { PaginationData } from '@/types/pagination';

export type LoanFilter = 'all' | 'overdue' | 'active' | 'returned';

export interface LoanHistoryRow {
    id: number;
    loanId: number;
    bookTitle: string;
    bookSlug: string;
    internalCode: string;
    borrowedAt: string;
    dueAt: string;
    returnedAt: string;
    status: string;
    statusLabel: string;
    isOverdue: boolean;
    isReturned: boolean;
}

export interface ReturnDraftPayload {
    id: number | null;
    status: string | null;
    itemsCount: number;
    expiresAt: string | null;
    expiresAtIso: string | null;
    hasActiveQr: boolean;
    qrCodeSvg: string | null;
    selectedLoanItemIds: number[];
    items: Array<{
        loanItemId: number;
        bookTitle: string;
        internalCode: string;
        borrowedAt: string;
        dueAt: string;
    }>;
}

export interface LoanHistoryPageProps {
    loans: PaginationData<LoanHistoryRow>;
    filters: {
        filter: LoanFilter;
        search: string;
    };
    stats: {
        total: number;
        active: number;
        overdue: number;
        returned: number;
    };
    returnDraft: ReturnDraftPayload;
}
