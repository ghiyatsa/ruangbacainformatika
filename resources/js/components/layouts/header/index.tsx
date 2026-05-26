import { usePage } from '@inertiajs/react';
import * as React from 'react';
import { useAppearance } from '@/hooks/use-appearance';
import { AppLogo } from './AppLogo';
import { DesktopNav } from './DesktopNav';
import { HeaderActions } from './HeaderActions';
import { MobileSheet } from './MobileSheet';
import type { Auth } from '@/types';

export function AppHeader({ hideSearch = false }: { hideSearch?: boolean }) {
    const { auth } = usePage<{
        auth: Auth;
    }>().props;
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const [mobileOpen, setMobileOpen] = React.useState(false);

    const currentUrl = usePage().url;

    const isActive = (href: string) => {
        if (href === '/') {
            return currentUrl === '/';
        }

        return currentUrl.startsWith(href);
    };

    React.useEffect(() => {
        const handleResize = () => {
            if (window.innerWidth >= 768) {
                setMobileOpen(false);
            }
        };

        window.addEventListener('resize', handleResize);

        return () => window.removeEventListener('resize', handleResize);
    }, []);

    return (
        <header className="sticky top-0 z-50 rounded-b-[1.15rem] border-b border-border/60 bg-background/88 backdrop-blur-md supports-backdrop-filter:bg-background/78 md:fixed md:top-4 md:left-0 md:w-full md:rounded-none md:border-b-0 md:bg-transparent md:backdrop-blur-none">
            <div className="mx-auto flex h-13 max-w-7xl items-center justify-between gap-1.5 px-3 transition-all duration-300 sm:h-16 sm:gap-2 sm:px-5 md:rounded-[1.15rem] md:border md:border-border/60 md:bg-background/88 md:shadow-lg md:shadow-black/5 md:backdrop-blur-md md:supports-backdrop-filter:bg-background/78 lg:gap-4 xl:gap-6 dark:md:border-white/10 dark:md:shadow-black/20">
                <div className="min-w-0 flex-1 items-center gap-4 md:flex lg:gap-5">
                    <AppLogo compact />
                    <DesktopNav isActive={isActive} />
                </div>

                <div className="flex shrink-0 items-center lg:gap-1">
                    <HeaderActions
                        auth={auth}
                        resolvedAppearance={resolvedAppearance}
                        updateAppearance={updateAppearance}
                        hideSearch={hideSearch}
                    />

                    <MobileSheet
                        mobileOpen={mobileOpen}
                        setMobileOpen={setMobileOpen}
                        isActive={isActive}
                        auth={auth}
                    />
                </div>
            </div>
        </header>
    );
}
