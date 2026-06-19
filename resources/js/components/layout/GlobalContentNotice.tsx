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

    if (variant === 'topbar') {
        return (
            <div
                className={cn(
                    'relative z-50 rounded-none border-b border-border/60 bg-background/95',
                    className,
                )}
            >
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

                    <div className="min-w-0 flex-1">
                        <p className="text-sm leading-relaxed font-medium text-foreground">
                            {notice.text}
                        </p>
                        {notice.url ? (
                            <a
                                href={notice.url}
                                className={`inline-flex text-sm font-semibold transition-colors ${noticeStyle.link}`}
                            >
                                {notice.linkLabel ?? 'Lihat detail'}
                            </a>
                        ) : null}
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
                'mb-6 rounded-2xl border border-border/70 bg-card/95 p-3 shadow-sm sm:p-4',
                className,
            )}
        >
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

                <div className="min-w-0 flex-1">
                    <p className="text-sm leading-relaxed font-medium text-foreground">
                        {notice.text}
                    </p>
                    {notice.url ? (
                        <a
                            href={notice.url}
                            className={`inline-flex text-sm font-semibold transition-colors ${noticeStyle.link}`}
                        >
                            {notice.linkLabel ?? 'Lihat detail'}
                        </a>
                    ) : null}
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

function isNoticeActiveAndNotDismissed(notice: GlobalNoticeData | null | undefined): boolean {
    if (!notice || !notice.isActive) {
        return false;
    }

    const key = getNoticeStorageKey(notice);

    if (typeof window !== 'undefined' && window.localStorage.getItem(key) === '1') {
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
            fallback={null}
        >
            <GlobalContentNotice
                className={className}
                variant={variant}
            />
        </Deferred>
    );
}
