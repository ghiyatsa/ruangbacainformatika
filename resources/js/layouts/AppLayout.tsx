import { usePage } from '@inertiajs/react';
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
        <div className="flex min-h-screen w-full flex-col">
            <GoogleOneTapPrompt />
            <DeferredGlobalContentNotice
                className="md:hidden"
                variant="topbar"
            />
            <AppHeader hideSearch={hideSearch} />
            <main
                className={`flex h-full w-full flex-1 flex-col ${isWelcome ? '' : 'md:pt-24'}`}
            >
                {children}
            </main>
            <Footer />
        </div>
    );
}
