import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { createElement } from 'react';
import { renderToString } from 'react-dom/server';
import { ErrorBoundary } from '@/components/common/ErrorBoundary';
import { LazyToaster } from '@/components/layout/LazyToaster';
import { useFlashToast } from '@/hooks/use-flash-toast';
import AppLayout from '@/layouts/AppLayout';
import AuthLayout from '@/layouts/AuthLayout';
import SettingsLayout from '@/layouts/SettingsLayout';
import type { ReactNode } from 'react';

function AppProviders({ children }: { children: ReactNode }) {
    useFlashToast();

    return (
        <ErrorBoundary>
            {children}
            <LazyToaster />
        </ErrorBoundary>
    );
}

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => (title ? `${title} - Ruang Baca` : 'Ruang Baca'),
        resolve: (name) => {
            const pages = import.meta.glob('./pages/**/*.tsx', { eager: true });
            const pageComponent = pages[`./pages/${name}.tsx`];

            if (!pageComponent) {
                throw new Error(`Page not found: ./pages/${name}.tsx`);
            }

            return (pageComponent as any).default;
        },
        layout: (name) => {
            switch (true) {
                case name === 'error/index':
                    return null;
                case name.startsWith('auth/'):
                    return AuthLayout;
                case name.startsWith('settings/'):
                    return [AppLayout, SettingsLayout];
                case name.startsWith('kiosk/'):
                    return null;
                default:
                    return AppLayout;
            }
        },
        setup: ({ App, props }: any) => {
            const app = createElement(App, props);

            return <AppProviders>{app}</AppProviders>;
        },
    })
);
