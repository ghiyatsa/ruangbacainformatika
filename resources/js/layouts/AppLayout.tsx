import { usePage } from '@inertiajs/react';
import { AppContent } from '@/components/layout/AppContent';
import { AppShell } from '@/components/layout/AppShell';
import Footer from '@/components/layout/footer';
import { DeferredGlobalContentNotice } from '@/components/layout/GlobalContentNotice';
import { AppHeader } from '@/components/layout/header';
import GoogleOneTapPrompt from '@/features/auth/components/GoogleOneTapPrompt';
import type { AppLayoutProps } from '@/types';

export default function AppLayout({
    children,
    hideSearch = false,
}: AppLayoutProps & { hideSearch?: boolean }) {
    const { component } = usePage();
    const isWelcome = component === 'welcome' || component === 'welcome/index';

    return (
        <AppShell variant="header">
            <GoogleOneTapPrompt />
            <DeferredGlobalContentNotice
                className="md:hidden"
                variant="topbar"
            />
            <AppHeader hideSearch={hideSearch} />
            <AppContent variant={isWelcome ? 'full' : 'header'}>
                {children}
            </AppContent>
            <Footer />
        </AppShell>
    );
}
