export type KioskStep = 'pin' | 'ready';

export type KioskMenu = 'landing' | 'visit' | 'member' | 'borrow' | 'return';

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

export interface FlashProps {
    [key: string]: unknown;
    flash?: {
        success?: string;
    };
}
