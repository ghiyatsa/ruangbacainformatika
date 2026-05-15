export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    whatsapp: string | null;
    address: string | null;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    canAccessAdminPanel?: boolean;
    homeUrl?: string;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
