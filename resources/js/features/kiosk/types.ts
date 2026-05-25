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
    memberRegistrationClaim?: KioskMemberRegistrationClaim | null;
}

export interface KioskMemberRegistrationClaim {
    id: number;
    name: string;
    email: string;
    whatsapp: string;
    address: string;
    linkUrl: string;
    qrSvg: string;
    status: 'pending' | 'linked' | 'claimed' | 'expired';
    expiresAt: string;
    claimedAt: string | null;
    lastErrorMessage: string | null;
    lastErrorAt: string | null;
    approvalPending: boolean;
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
