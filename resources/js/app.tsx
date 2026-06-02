import { createInertiaApp } from '@inertiajs/react';
import { createElement } from 'react';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { LazyToaster } from '@/components/app/LazyToaster';
import { ErrorBoundary } from '@/components/common/ErrorBoundary';
import { initializeTheme } from '@/hooks/use-appearance';
import { useFlashToast } from '@/hooks/use-flash-toast';
import AppLayout from '@/layouts/AppLayout';
import AuthLayout from '@/layouts/AuthLayout';
import SettingsLayout from '@/layouts/SettingsLayout';
import type { ReactNode } from 'react';
import type { Root } from 'react-dom/client';

const rootDataset = document.documentElement.dataset;
const appName =
    rootDataset.appName || import.meta.env.VITE_APP_NAME || 'Ruang Baca';
const cspNonce =
    document
        .querySelector<HTMLMetaElement>('meta[name="csp-nonce"]')
        ?.content.trim() || undefined;

type RootElement = HTMLElement & {
    __inertiaRoot?: Root;
    __inertiaHydrated?: boolean;
};

function applyStyleNonce(nonce?: string) {
    if (!nonce || typeof document === 'undefined') {
        return;
    }

    const originalCreateElement = document.createElement.bind(document);

    document.createElement = function patchedCreateElement(
        tagName: string,
        options?: ElementCreationOptions,
    ) {
        const element = originalCreateElement(tagName, options);

        if (
            tagName.toLowerCase() === 'style' &&
            element instanceof HTMLStyleElement
        ) {
            element.nonce = nonce;
        }

        return element;
    };
}

function AppProviders({ children }: { children: ReactNode }) {
    useFlashToast();

    return (
        <ErrorBoundary>
            {children}
            <LazyToaster />
        </ErrorBoundary>
    );
}

applyStyleNonce(cspNonce);

createInertiaApp({
    nonce: cspNonce,
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
