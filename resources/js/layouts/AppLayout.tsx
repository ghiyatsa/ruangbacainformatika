import * as React from 'react';
import { BackgroundPattern } from '@/components/layout/BackgroundPattern';
import Footer from '@/components/layout/footer';
import { DeferredGlobalContentNotice } from '@/components/layout/GlobalContentNotice';
import { AppHeader } from '@/components/layout/header';
import GoogleOneTapPrompt from '@/features/auth/components/GoogleOneTapPrompt';
import type { AppLayoutProps } from '@/types';

export default function AppLayout({
    children,
    hideSearch = false,
}: AppLayoutProps & { hideSearch?: boolean }) {
    const headerGroupRef = React.useRef<HTMLDivElement>(null);
    const [visible, setVisible] = React.useState(true);
    const lastScrollY = React.useRef(0);

    React.useLayoutEffect(() => {
        if (!headerGroupRef.current) {
            return;
        }

        const updateHeight = () => {
            if (headerGroupRef.current) {
                const rect = headerGroupRef.current.getBoundingClientRect();
                document.documentElement.style.setProperty(
                    '--header-height',
                    `${rect.height}px`,
                );
            }
        };

        updateHeight();

        window.addEventListener('resize', updateHeight);

        const observer = new ResizeObserver(updateHeight);
        observer.observe(headerGroupRef.current);

        return () => {
            window.removeEventListener('resize', updateHeight);
            observer.disconnect();
        };
    }, []);

    React.useEffect(() => {
        lastScrollY.current = window.scrollY;

        const handleScroll = () => {
            const currentScrollY = window.scrollY;

            if (currentScrollY <= 20) {
                setVisible(true);
            } else if (currentScrollY > lastScrollY.current) {
                setVisible(false);
            } else if (currentScrollY < lastScrollY.current) {
                setVisible(true);
            }

            lastScrollY.current = currentScrollY;
        };

        window.addEventListener('scroll', handleScroll, { passive: true });

        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <div className="relative flex min-h-screen w-full flex-col">
            <BackgroundPattern />
            <div className="pointer-events-none absolute top-0 bottom-0 left-1/2 z-20 w-full max-w-7xl -translate-x-1/2 border-x border-border/60" />
            <GoogleOneTapPrompt />
            <div
                ref={headerGroupRef}
                className={`sticky top-0 z-60 flex w-full shrink-0 flex-col transition-transform duration-300 ease-in-out ${visible ? 'translate-y-0' : '-translate-y-full'}`}
            >
                <DeferredGlobalContentNotice variant="topbar" />
                <AppHeader hideSearch={hideSearch} />
            </div>
            <main className="flex h-full w-full flex-1 flex-col">
                {children}
            </main>
            <div className="w-full border-y border-border/60">
                <div
                    className="mx-auto h-6 max-w-7xl px-4 sm:h-8 sm:px-6 lg:px-8"
                    style={{
                        backgroundImage:
                            'repeating-linear-gradient(-45deg, var(--color-border) 0, var(--color-border) 1px, transparent 1px, transparent 12px)',
                    }}
                />
            </div>
            <Footer />
        </div>
    );
}
