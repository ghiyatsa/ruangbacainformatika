import type { Auth, LoanRequestCart } from '@/types/auth';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            site: {
                url: string;
                description: string;
                department: string;
                contactEmail: string;
                address: string;
                ogImage: string;
            };
            auth: Auth;
            loanRequestCart: LoanRequestCart | null;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
