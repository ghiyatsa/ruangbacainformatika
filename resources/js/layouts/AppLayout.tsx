import { usePage } from '@inertiajs/react';
import * as React from 'react';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { BackgroundPattern } from '@/components/layout/BackgroundPattern';
import Footer from '@/components/layout/footer';
import { DeferredGlobalContentNotice } from '@/components/layout/GlobalContentNotice';
import { AppHeader } from '@/components/layout/header';
import GoogleOneTapPrompt from '@/features/auth/components/GoogleOneTapPrompt';
import { syncColorPalette } from '@/hooks/use-appearance';
import type { AppLayoutProps } from '@/types';

type BreadcrumbsStoreListener = () => void;
let currentBreadcrumbs: AppLayoutProps['breadcrumbs'] = [];
const listeners = new Set<BreadcrumbsStoreListener>();

export function setPageBreadcrumbs(breadcrumbs: AppLayoutProps['breadcrumbs']) {
    currentBreadcrumbs = breadcrumbs;
    listeners.forEach((listener) => listener());
}

function useBreadcrumbs(initial?: AppLayoutProps['breadcrumbs']) {
    return React.useSyncExternalStore(
        (listener) => {
            listeners.add(listener);

            return () => {
                listeners.delete(listener);
            };
        },
        () => currentBreadcrumbs ?? initial,
        () => initial,
    );
}

export default function AppLayout({
    children,
    hideSearch = false,
    breadcrumbs: propBreadcrumbs,
}: AppLayoutProps & { hideSearch?: boolean }) {
    const page = usePage();
    const colorPalette = page.props.site?.colorPalette;

    React.useEffect(() => {
        syncColorPalette(colorPalette);
    }, [colorPalette]);

    const breadcrumbs = useBreadcrumbs(propBreadcrumbs);
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
    }, [breadcrumbs]);

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
            <a
                href="#main-content"
                className="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-100 focus:rounded-lg focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-primary-foreground"
            >
                Lewati ke konten utama
            </a>
            <GoogleOneTapPrompt />
            <div
                ref={headerGroupRef}
                className={`sticky top-0 z-60 flex w-full shrink-0 flex-col transition-transform duration-300 ease-in-out ${visible ? 'translate-y-0' : '-translate-y-full'}`}
            >
                <DeferredGlobalContentNotice variant="topbar" />
                <AppHeader hideSearch={hideSearch} />
                {breadcrumbs && breadcrumbs.length > 0 && (
                    <div className="relative z-10 w-full border-b border-border/60 bg-background/95 backdrop-blur-xs">
                        <div className="mx-auto flex max-w-7xl items-center border-x border-border/60 bg-muted/5 px-4 py-2.5 sm:px-6 lg:px-8">
                            <Breadcrumbs breadcrumbs={breadcrumbs} />
                        </div>
                    </div>
                )}
            </div>
            <main
                id="main-content"
                className="flex h-full w-full flex-1 flex-col"
            >
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
