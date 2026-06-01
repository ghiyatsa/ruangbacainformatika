import { usePage } from '@inertiajs/react';
import { Bell, X } from 'lucide-react';
import * as React from 'react';
import { useAppearance } from '@/hooks/use-appearance';
import { AppLogo } from './AppLogo';
import { DesktopNav } from './DesktopNav';
import { HeaderActions } from './HeaderActions';
import { MobileSheet } from './MobileSheet';
import type { Auth } from '@/types';

const NOTICE_STYLES = {
    info: {
        icon: 'text-primary',
        ping: 'bg-primary/60',
        dot: 'bg-primary',
        link: 'text-primary hover:text-primary/80',
    },
    warning: {
        icon: 'text-amber-600 dark:text-amber-400',
        ping: 'bg-amber-500/60 dark:bg-amber-400/60',
        dot: 'bg-amber-500 dark:bg-amber-400',
        link: 'text-amber-700 hover:text-amber-600 dark:text-amber-400 dark:hover:text-amber-300',
    },
    success: {
        icon: 'text-emerald-600 dark:text-emerald-400',
        ping: 'bg-emerald-500/60 dark:bg-emerald-400/60',
        dot: 'bg-emerald-500 dark:bg-emerald-400',
        link: 'text-emerald-700 hover:text-emerald-600 dark:text-emerald-400 dark:hover:text-emerald-300',
    },
} as const;

export function AppHeader({ hideSearch = false }: { hideSearch?: boolean }) {
    const page = usePage<{
        auth: Auth;
        site: {
            notice: {
                isActive: boolean;
                text: string;
                url: string | null;
                linkLabel: string | null;
                tone: 'info' | 'warning' | 'success';
            };
        };
    }>();
    const { auth, site } = page.props;
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const [mobileOpen, setMobileOpen] = React.useState(false);
    const [dismissedNoticeKey, setDismissedNoticeKey] = React.useState<string | null>(null);

    const currentUrl = page.url;
    const notice = site.notice;
    const noticeStyle = NOTICE_STYLES[notice.tone];
    const noticeStorageKey = React.useMemo(
        () =>
            `mobile-notice:${notice.tone}:${notice.text}:${notice.url ?? ''}:${notice.linkLabel ?? ''}`,
        [notice.linkLabel, notice.text, notice.tone, notice.url],
    );
    const isNoticeDismissed =
        dismissedNoticeKey === noticeStorageKey
        || (typeof window !== 'undefined'
            && window.localStorage.getItem(noticeStorageKey) === '1');

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

    const closeNotice = React.useCallback(() => {
        window.localStorage.setItem(noticeStorageKey, '1');
        setDismissedNoticeKey(noticeStorageKey);
    }, [noticeStorageKey]);

    return (
        <>
            {notice.isActive && !isNoticeDismissed && (
                <div className="border-b border-border/60 bg-muted/85 backdrop-blur-md supports-backdrop-filter:bg-muted/75 md:hidden">
                    <div className="mx-auto flex max-w-7xl items-start gap-3 px-4 py-2.5 sm:px-5">
                        <span
                            className={`relative mt-0.5 flex size-8 shrink-0 items-center justify-center transition-transform duration-200 ${noticeStyle.icon}`}
                        >
                            <Bell className="origin-top motion-safe:animate-(--animate-bell-swing)" />
                            <span className="absolute top-1.5 right-1.5 flex size-2">
                                <span
                                    className={`absolute inline-flex h-full w-full animate-ping rounded-full ${noticeStyle.ping}`}
                                />
                                <span
                                    className={`relative inline-flex size-2 rounded-full ${noticeStyle.dot}`}
                                />
                            </span>
                        </span>

                        <div className="min-w-0 flex-1 text-left text-sm">
                            <p className="font-medium text-foreground">
                                {notice.text}
                            </p>

                            {notice.url && (
                                <a
                                    href={notice.url}
                                    className={`mt-1 inline-flex font-semibold transition-colors ${noticeStyle.link}`}
                                >
                                    {notice.linkLabel ?? 'Lihat detail'}
                                </a>
                            )}
                        </div>

                        <button
                            type="button"
                            onClick={closeNotice}
                            className="inline-flex size-8 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            aria-label="Tutup notifikasi"
                        >
                            <X className="size-4" />
                        </button>
                    </div>
                </div>
            )}

            <header className="sticky top-0 z-50 w-full border-b border-border/60 bg-background/80 shadow-sm shadow-black/5 backdrop-blur-md supports-backdrop-filter:bg-background/70 md:fixed md:top-4 md:left-0 md:border-b-0 md:bg-transparent md:shadow-none md:backdrop-blur-none">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-2 bg-transparent px-4 transition-all duration-300 sm:px-5 md:rounded-[1.15rem] md:border md:border-border/60 md:bg-background/88 md:shadow-lg md:shadow-black/5 md:backdrop-blur-md md:supports-backdrop-filter:bg-background/78 lg:gap-4 xl:gap-6 dark:md:border-white/10 dark:md:shadow-black/20">
                    <div className="flex min-w-0 flex-1 items-center gap-0.5 md:gap-4 lg:gap-5">
                        <MobileSheet
                            mobileOpen={mobileOpen}
                            setMobileOpen={setMobileOpen}
                            isActive={isActive}
                            auth={auth}
                        />
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
                    </div>
                </div>
            </header>
        </>
    );
}
