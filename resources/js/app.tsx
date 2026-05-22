import { createInertiaApp } from '@inertiajs/react';
import { createElement } from 'react';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { ErrorBoundary } from '@/components/common/ErrorBoundary';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import { useFlashToast } from '@/hooks/use-flash-toast';
import AppLayout from '@/layouts/AppLayout';
import AuthLayout from '@/layouts/AuthLayout';
import SettingsLayout from '@/layouts/SettingsLayout';
import type { ReactNode } from 'react';
import type { Root } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Ruang Baca';

type RootElement = HTMLElement & {
    __inertiaRoot?: Root;
    __inertiaHydrated?: boolean;
};

function AppProviders({ children }: { children: ReactNode }) {
    useFlashToast();

    return (
        <ErrorBoundary>
            <TooltipProvider>
                {children}
                <Toaster />
            </TooltipProvider>
        </ErrorBoundary>
    );
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'error-page':
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
    strictMode: true,
    setup({ el, App, props }) {
        const rootElement = el as RootElement;
        const app = createElement(App, props);
        const wrappedApp = <AppProviders>{app}</AppProviders>;

        if (
            rootElement.hasAttribute('data-server-rendered') &&
            !rootElement.__inertiaHydrated
        ) {
            hydrateRoot(rootElement, wrappedApp);
            rootElement.__inertiaHydrated = true;

            return;
        }

        if (!rootElement.__inertiaRoot) {
            rootElement.__inertiaRoot = createRoot(rootElement);
        }

        rootElement.__inertiaRoot.render(wrappedApp);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
