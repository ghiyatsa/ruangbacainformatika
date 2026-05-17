import { usePage } from '@inertiajs/react';
import * as React from 'react';
import { useAppearance } from '@/hooks/use-appearance';
import type { Auth } from '@/types';
import { AppLogo } from './AppLogo';
import { DesktopNav } from './DesktopNav';
import { HeaderActions } from './HeaderActions';
import { MobileSheet } from './MobileSheet';

export function AppHeader({ hideSearch = false }: { hideSearch?: boolean }) {
    const { auth, canRegister = true } = usePage<{
        auth: Auth;
        canRegister?: boolean;
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
        <header className="fixed top-3 z-50 w-full px-3 sm:top-5 sm:px-5">
            <div className="mx-auto flex h-14 max-w-7xl items-center justify-between gap-2 rounded-2xl border border-border/50 bg-background/80 px-3 shadow-xl backdrop-blur-xl transition-all duration-300 sm:h-16 sm:px-5 lg:gap-4 xl:gap-6 dark:border-white/10 dark:bg-background/40">
                <div className="min-w-0 flex-1 items-center gap-4 md:flex lg:gap-5">
                    <AppLogo compact />
                    <DesktopNav isActive={isActive} />
                </div>

                <div className="flex shrink-0 items-center gap-1 lg:gap-2">
                    <HeaderActions
                        auth={auth}
                        canRegister={canRegister}
                        resolvedAppearance={resolvedAppearance}
                        updateAppearance={updateAppearance}
                        hideSearch={hideSearch}
                    />

                    <MobileSheet
                        mobileOpen={mobileOpen}
                        setMobileOpen={setMobileOpen}
                        isActive={isActive}
                        auth={auth}
                        canRegister={canRegister}
                    />
                </div>
            </div>
        </header>
    );
}
