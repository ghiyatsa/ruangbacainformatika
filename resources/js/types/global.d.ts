import type { Auth, LoanRequestCart } from '@/types/auth';
import type { NotificationSummary } from '@/types/notifications';

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
            notifications: NotificationSummary;
            loanRequestCart: LoanRequestCart | null;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
