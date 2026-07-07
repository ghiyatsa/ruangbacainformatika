import { Deferred, usePage } from '@inertiajs/react';
import { Bell, X } from 'lucide-react';
import * as React from 'react';
import { cn } from '@/lib/utils';

export interface GlobalNoticeData {
    isActive: boolean;
    text: string;
    url: string | null;
    linkLabel: string | null;
    tone: 'info' | 'warning' | 'success';
}

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

interface GlobalContentNoticeProps {
    className?: string;
    notice?: GlobalNoticeData | null;
    variant?: 'card' | 'topbar';
}

function getNoticeStorageKey(notice: GlobalNoticeData): string {
    return `content-notice:${notice.tone}:${notice.text}:${notice.url ?? ''}:${notice.linkLabel ?? ''}`;
}

export function GlobalContentNotice({
    className,
    notice: providedNotice,
    variant = 'card',
}: GlobalContentNoticeProps = {}) {
    const page = usePage<{
        site?: { notice?: GlobalNoticeData | null };
    }>();
    const notice = providedNotice ?? page.props.site?.notice;

    const noticeStorageKey = React.useMemo(() => {
        return notice ? getNoticeStorageKey(notice) : '';
    }, [notice]);

    const [dismissedNoticeKey, setDismissedNoticeKey] = React.useState<
        string | null
    >(null);

    const containerRef = React.useRef<HTMLDivElement>(null);
    const textRef = React.useRef<HTMLDivElement>(null);
    const [isOverflowing, setIsOverflowing] = React.useState(false);

    React.useEffect(() => {
        const container = containerRef.current;
        const text = textRef.current;

        if (!container || !text || !notice) {
            return;
        }

        const checkOverflow = () => {
            setIsOverflowing(text.scrollWidth > container.clientWidth);
        };

        checkOverflow();
        
        // Wait a brief moment to ensure layouts have settled
        const timer = window.setTimeout(checkOverflow, 50);

        window.addEventListener('resize', checkOverflow);

        return () => {
            window.clearTimeout(timer);
            window.removeEventListener('resize', checkOverflow);
        };
    }, [notice?.text, notice]);

    const closeNotice = React.useCallback(() => {
        if (noticeStorageKey) {
            window.localStorage.setItem(noticeStorageKey, '1');
            setDismissedNoticeKey(noticeStorageKey);
        }
    }, [noticeStorageKey]);

    if (!notice) {
        return null;
    }

    const noticeStyle = NOTICE_STYLES[notice.tone];
    const isNoticeDismissed =
        dismissedNoticeKey === noticeStorageKey ||
        (typeof window !== 'undefined' &&
            noticeStorageKey !== '' &&
            window.localStorage.getItem(noticeStorageKey) === '1');

    if (!notice.isActive || isNoticeDismissed) {
        return null;
    }

    const marqueeStyle = isOverflowing ? (
        <style
            dangerouslySetInnerHTML={{
                __html: `
                    @keyframes notice-marquee {
                        0% { transform: translate3d(0, 0, 0); }
                        100% { transform: translate3d(-50%, 0, 0); }
                    }
                    .animate-notice-marquee {
                        display: inline-flex;
                        white-space: nowrap;
                        animation: notice-marquee 20s linear infinite;
                    }
                `,
            }}
        />
    ) : null;

    const textContent = (
        <span className="inline-flex items-center gap-2 whitespace-nowrap">
            <span>{notice.text}</span>
        </span>
    );

    const linkContent = notice.url ? (
        <a
            href={notice.url}
            className={`inline-flex text-xs font-semibold leading-tight transition-colors ${noticeStyle.link}`}
        >
            {notice.linkLabel ?? 'Lihat detail'}
        </a>
    ) : null;

    if (variant === 'topbar') {
        return (
            <div
                className={cn(
                    'relative z-50 rounded-none border-b border-border/60 bg-background',
                    className,
                )}
            >
                {marqueeStyle}
                <div className="mx-auto flex max-w-7xl items-center gap-3 border-x border-border/60 px-4 py-2 sm:px-6">
                    <span
                        className={`relative mt-0.5 flex size-7 shrink-0 items-center justify-center ${noticeStyle.icon}`}
                    >
                        <Bell className="size-4" />
                        <span className="absolute top-1.5 right-1.5 flex size-2">
                            <span
                                className={`absolute inline-flex h-full w-full animate-ping rounded-full ${noticeStyle.ping}`}
                            />
                            <span
                                className={`relative inline-flex size-2 rounded-full ${noticeStyle.dot}`}
                            />
                        </span>
                    </span>

                    <div className="min-w-0 flex-1 flex flex-col justify-center">
                        <div ref={containerRef} className="w-full overflow-hidden">
                            <div
                                ref={textRef}
                                className={cn(
                                    'text-sm font-medium text-foreground',
                                    isOverflowing ? 'animate-notice-marquee gap-8 pr-8' : 'flex items-center'
                                )}
                            >
                                {textContent}
                                {isOverflowing && textContent}
                            </div>
                        </div>
                        {linkContent}
                    </div>

                    <button
                        type="button"
                        onClick={closeNotice}
                        className="inline-flex size-7 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        aria-label="Tutup notifikasi"
                    >
                        <X className="size-4" />
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div
            className={cn(
                'mb-6 rounded-2xl border border-border/70 bg-card p-3 shadow-sm sm:p-4',
                className,
            )}
        >
            {marqueeStyle}
            <div className="flex items-center gap-3">
                <span
                    className={`relative mt-0.5 flex size-8 shrink-0 items-center justify-center ${noticeStyle.icon}`}
                >
                    <Bell className="size-4" />
                    <span className="absolute top-1.5 right-1.5 flex size-2">
                        <span
                            className={`absolute inline-flex h-full w-full animate-ping rounded-full ${noticeStyle.ping}`}
                        />
                        <span
                            className={`relative inline-flex size-2 rounded-full ${noticeStyle.dot}`}
                        />
                    </span>
                </span>

                <div className="min-w-0 flex-1 flex flex-col justify-center">
                    <div ref={containerRef} className="w-full overflow-hidden">
                        <div
                            ref={textRef}
                            className={cn(
                                'text-sm font-medium text-foreground',
                                isOverflowing ? 'animate-notice-marquee gap-8 pr-8' : 'flex items-center'
                            )}
                        >
                            {textContent}
                            {isOverflowing && textContent}
                        </div>
                    </div>
                    {linkContent}
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
    );
}

function isNoticeActiveAndNotDismissed(
    notice: GlobalNoticeData | null | undefined,
): boolean {
    if (!notice || !notice.isActive) {
        return false;
    }

    const key = getNoticeStorageKey(notice);

    if (
        typeof window !== 'undefined' &&
        window.localStorage.getItem(key) === '1'
    ) {
        return false;
    }

    return true;
}

export function DeferredGlobalContentNotice({
    className,
    variant = 'card',
}: Pick<GlobalContentNoticeProps, 'className' | 'variant'> = {}) {
    const page = usePage<{
        site?: { notice: GlobalNoticeData };
    }>();

    const siteNotice = page.props.site?.notice;
    const isNoticeVisible = isNoticeActiveAndNotDismissed(siteNotice);

    if (!isNoticeVisible) {
        return null;
    }

    return (
        <Deferred
            data="globalNotice"
            fallback={
                <GlobalContentNoticeSkeleton
                    className={className}
                    variant={variant}
                />
            }
        >
            <DeferredGlobalContentNoticeContent
                className={className}
                variant={variant}
            />
        </Deferred>
    );
}

interface GlobalContentNoticeSkeletonProps {
    className?: string;
    variant?: 'card' | 'topbar';
}

export function GlobalContentNoticeSkeleton({
    className,
    variant = 'card',
}: GlobalContentNoticeSkeletonProps) {
    const style = {
        animation: 'fadeInNoticeSkeleton 0.15s ease-out forwards',
    };

    if (variant === 'topbar') {
        return (
            <>
                <style
                    dangerouslySetInnerHTML={{
                        __html: `
                    @keyframes fadeInNoticeSkeleton {
                        from { opacity: 0; transform: translateY(-4px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                `,
                    }}
                />
                <div
                    style={style}
                    className={cn(
                        'rounded-none border-b border-border/60 bg-background',
                        className,
                    )}
                >
                    <div className="mx-auto flex max-w-7xl items-center gap-3 border-x border-border/60 px-4 py-2 sm:px-6">
                        <span className="relative mt-0.5 flex size-7 shrink-0 items-center justify-center text-muted-foreground/40">
                            <Bell className="size-4 animate-pulse" />
                        </span>

                        <div className="min-w-0 flex-1 space-y-2 py-1">
                            <div className="h-3 w-40 animate-pulse rounded-full bg-muted/80" />
                            <div className="h-2.5 w-24 animate-pulse rounded-full bg-muted/60" />
                        </div>

                        <div className="size-7 shrink-0 animate-pulse rounded-full bg-muted/40" />
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <style
                dangerouslySetInnerHTML={{
                    __html: `
                @keyframes fadeInNoticeSkeleton {
                    from { opacity: 0; transform: scale(0.98); }
                    to { opacity: 1; transform: scale(1); }
                }
            `,
                }}
            />
            <div
                style={style}
                className={cn(
                    'mb-6 rounded-2xl border border-border/70 bg-card p-3 shadow-sm sm:p-4',
                    className,
                )}
            >
                <div className="flex items-center gap-3">
                    <span className="relative mt-0.5 flex size-8 shrink-0 items-center justify-center text-muted-foreground/40">
                        <Bell className="size-4 animate-pulse" />
                    </span>

                    <div className="min-w-0 flex-1 space-y-2 py-1">
                        <div className="h-3 w-1/2 animate-pulse rounded-full bg-muted/80" />
                        <div className="h-2.5 w-1/4 animate-pulse rounded-full bg-muted/60" />
                    </div>

                    <div className="size-8 shrink-0 animate-pulse rounded-full bg-muted/40" />
                </div>
            </div>
        </>
    );
}

function DeferredGlobalContentNoticeContent({
    className,
    variant = 'card',
}: Pick<GlobalContentNoticeProps, 'className' | 'variant'>) {
    const page = usePage<{
        globalNotice?: GlobalNoticeData | null;
    }>();

    const notice = page.props.globalNotice;
    const [prevNotice, setPrevNotice] = React.useState<
        GlobalNoticeData | null | undefined
    >(notice);
    const [activeNotice, setActiveNotice] =
        React.useState<GlobalNoticeData | null>(notice ?? null);

    if (notice !== prevNotice) {
        setPrevNotice(notice);

        if (notice !== undefined && notice !== null) {
            setActiveNotice(notice);
        }
    }

    if (!activeNotice) {
        return null;
    }

    return (
        <GlobalContentNotice
            className={className}
            notice={activeNotice}
            variant={variant}
        />
    );
}
