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
            <header className="sticky top-0 z-60 w-full border-b border-border/60 bg-background">
                <div className="mx-auto flex h-18 max-w-7xl items-center justify-between gap-2 border-x border-border/60 px-4 sm:px-6 lg:gap-4 lg:px-8 xl:gap-6">
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
