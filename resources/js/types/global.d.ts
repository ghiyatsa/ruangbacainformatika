import type { Auth } from '@/types/auth';
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
                ogImageType: string;
                ogImageWidth: number;
                ogImageHeight: number;
                colorPalette?: string;
                notice: {
                    isActive: boolean;
                    text: string;
                    url: string | null;
                    linkLabel: string | null;
                    tone: 'info' | 'warning' | 'success';
                };
            };
            auth: Auth;
            notifications: NotificationSummary;
            [key: string]: unknown;
        };
    }
}
