import type { Auth, GoogleAuth } from '@/types/auth';
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
                logo?: string;
                notice: {
                    isActive: boolean;
                    text: string;
                    url: string | null;
                    linkLabel: string | null;
                    tone: 'info' | 'warning' | 'success';
                };
            };
            /**
             * Metadata SEO spesifik halaman dari server (og:image buku/skripsi/
             * tesis/laporan KP). Dikirim sebagai prop agar komponen React memakai
             * gambar OG dokumen alih-alih gambar OG generik situs.
             */
            meta?: {
                title?: string;
                description?: string;
                keywords?: string | null;
                robots?: string;
                canonicalUrl?: string;
                type?: 'website' | 'article';
                ogImage?: string;
                ogImageType?: string;
                ogImageWidth?: number;
                ogImageHeight?: number;
            };
            auth: Auth;
            googleAuth?: GoogleAuth;
            notifications: NotificationSummary;
            [key: string]: unknown;
        };
    }
}
