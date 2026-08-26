export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    whatsapp: string | null;
    whatsapp_verified_at?: string | null;
    address: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type BorrowingAccessReason = {
    title: string;
    message: string;
    actionUrl: string | null;
};

export type BorrowingAccess = {
    canBorrow: boolean;
    reason: BorrowingAccessReason | null;
};

export type Auth = {
    user: User | null;
    canAccessAdminPanel?: boolean;
    canBorrowBooks?: boolean;
    canViewNotifications?: boolean;
    hasVerifiedWhatsApp?: boolean;
    borrowingAccess?: BorrowingAccess;
    isMember?: boolean;
};

export type GoogleAuth = {
    clientId: string | null;
    loginUrl: string;
    oneTapUrl: string;
    enabled: boolean;
    oneTapEnabled: boolean;
};

export type LoanRequestCart = {
    count: number;
    maxBooks: number;
    activeLoansCount: number;
    hasActiveQr: boolean;
    bookIds: number[];
};
