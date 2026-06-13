import { usePage } from '@inertiajs/react';
import * as React from 'react';
import { useAppearance } from '@/hooks/use-appearance';
import { AppLogo } from './AppLogo';
import { DesktopNav } from './DesktopNav';
import { HeaderActions } from './HeaderActions';
import { MobileSheet } from './MobileSheet';
import type { Auth } from '@/types';

export function AppHeader({ hideSearch = false }: { hideSearch?: boolean }) {
    const page = usePage<{
        auth: Auth;
    }>();
    const { auth } = page.props;
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const [mobileOpen, setMobileOpen] = React.useState(false);

    const currentUrl = page.url;

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
        <>
            <header className="sticky top-0 z-[60] w-full border-b border-border/60 bg-background shadow-sm shadow-black/5 md:fixed md:top-4 md:left-0 md:border-b-0 md:bg-transparent md:shadow-none">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-2 bg-background px-3 transition-all duration-300 sm:px-5 md:rounded-[1.15rem] md:border md:border-border/60 md:shadow-lg md:shadow-black/5 lg:gap-4 xl:gap-6 dark:md:border-white/10 dark:md:shadow-black/20">
                    <div className="flex min-w-0 flex-1 items-center gap-0 md:gap-4 lg:gap-5">
                        <MobileSheet
                            mobileOpen={mobileOpen}
                            setMobileOpen={setMobileOpen}
                            isActive={isActive}
                            auth={auth}
                        />
                        <AppLogo compact />
                        <DesktopNav isActive={isActive} />
                    </div>

                    <div className="flex shrink-0 items-center gap-1 lg:gap-1">
                        <HeaderActions
                            auth={auth}
                            resolvedAppearance={resolvedAppearance}
                            updateAppearance={updateAppearance}
                            hideSearch={hideSearch}
                        />
                    </div>
                </div>
            </header>
        </>
    );
}
