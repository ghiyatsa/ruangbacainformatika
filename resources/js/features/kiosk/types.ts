export type KioskStep = 'pin' | 'ready';

export type KioskMenu = 'landing' | 'visit' | 'member' | 'borrow' | 'return';

export type KioskBookSearchMode = 'borrow' | 'return';

export interface KioskProps {
    step: KioskStep;
    activeMenu: KioskMenu;
    pageTitle: string;
    pageSubtitle: string;
    siteName: string;
    siteTagline: string;
    loanMaxBooks: number;
    visitorTypeOptions: Record<string, string>;
    purposeOptions: Record<string, string>;
}

export interface KioskBookSearchResult {
    id: number;
    title: string;
    slug: string;
    isbn: string | null;
    issn: string | null;
    coverImageUrl?: string;
    authors?: string[];
    availableItemsCount: number;
    isBorrowable: boolean;
    isAvailable: boolean;
}

export interface FlashProps {
    [key: string]: unknown;
    flash?: {
        success?: string;
    };
}
