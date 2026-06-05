import { usePage } from '@inertiajs/react';
import GoogleOneTapPrompt from '@/components/auth/GoogleOneTapPrompt';
import { AppContent } from '@/components/layouts/AppContent';
import { AppShell } from '@/components/layouts/AppShell';
import Footer from '@/components/layouts/footer';
import { DeferredGlobalContentNotice } from '@/components/layouts/GlobalContentNotice';
import { AppHeader } from '@/components/layouts/header';
import type { AppLayoutProps } from '@/types';

export default function AppLayout({
    children,
    hideSearch = false,
}: AppLayoutProps & { hideSearch?: boolean }) {
    const { component } = usePage();
    const isWelcome = component === 'welcome';

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
