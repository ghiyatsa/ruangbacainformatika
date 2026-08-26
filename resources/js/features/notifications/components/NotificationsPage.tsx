import { router } from '@inertiajs/react';
import { ArrowUpRight, CheckCheck, Inbox } from 'lucide-react';
import { useState } from 'react';
import * as NotificationController from '@/actions/App/Http/Controllers/NotificationController';
import { PageLayout } from '@/components/layout/PageLayout';
import { Button } from '@/components/ui/button';
import { CatalogPagination } from '@/features/books/components/CatalogPagination';
import {
    NotificationIcon,
    formatNotificationTime,
    notificationKindLabel,
} from '@/features/notifications/components/notification-ui';
import { getCsrfToken } from '@/lib/csrf';
import { cn } from '@/lib/utils';
import type { SiteNotification } from '@/types';
import type { PaginationData } from '@/types/pagination';

interface NotificationsPageProps {
    notifications: PaginationData<SiteNotification>;
}

export function NotificationsPage({ notifications }: NotificationsPageProps) {
    const [items, setItems] = useState(notifications.data);

    const markAsRead = async (
        notificationId: string,
    ): Promise<number | null> => {
        const csrfToken = getCsrfToken();
        const response = await fetch(
            NotificationController.markAsRead.url(notificationId),
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
            },
        );

        if (!response.ok) {
            return null;
        }

        return (await response.json()) as number;
    };

    const handleNotificationClick = (notification: SiteNotification): void => {
        if (notification.readAt === null) {
            void markAsRead(notification.id).then((unreadCount) => {
                if (unreadCount !== null) {
                    setItems((current) =>
                        current.map((item) =>
                            item.id === notification.id
                                ? {
                                      ...item,
                                      readAt: new Date().toISOString(),
                                  }
                                : item,
                        ),
                    );
                }
            });
        }

        if (notification.actionUrl) {
            router.visit(notification.actionUrl);
        }
    };

    const markAllAsRead = async (): Promise<void> => {
        const csrfToken = getCsrfToken();
        const response = await fetch(
            NotificationController.markAllAsRead.url(),
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
            },
        );

        if (!response.ok) {
            return;
        }

        setItems((current) =>
            current.map((notification) => ({
                ...notification,
                readAt: notification.readAt ?? new Date().toISOString(),
            })),
        );
    };

    const unreadCount = items.filter(
        (notification) => notification.readAt === null,
    ).length;

    return (
        <PageLayout
            title="Notifikasi"
            metaDescription="Notifikasi akun dan peminjaman akun Anda di Ruang Baca Informatika."
            maxWidth="7xl"
            showDesktopNoticeInContent={false}
            header={
                <div className="relative -mt-20 overflow-hidden bg-background sm:-mt-28 md:-mt-24">
                    <div className="relative mx-auto max-w-7xl px-4 pt-24 pb-12 sm:px-6 sm:pt-30 lg:px-8">
                        <h1 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                            Notifikasi
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Riwayat notifikasi akun dan peminjaman Anda.
                        </p>
                    </div>
                </div>
            }
        >
            <div className="relative z-10 space-y-4">
                <div className="flex items-center justify-end">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={unreadCount === 0}
                        onClick={() => void markAllAsRead()}
                    >
                        <CheckCheck className="mr-1 size-3.5" />
                        Tandai semua dibaca
                    </Button>
                </div>

                {items.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-border/70 bg-card px-6 py-14 text-center">
                        <div className="mx-auto mb-4 flex size-12 items-center justify-center rounded-2xl bg-primary/8 text-primary">
                            <Inbox className="size-5" />
                        </div>
                        <p className="text-sm font-medium text-foreground">
                            Belum ada notifikasi
                        </p>
                        <p className="mx-auto mt-1 max-w-xs text-xs leading-relaxed text-muted-foreground">
                            Notifikasi akun dan peminjaman akan muncul di sini.
                        </p>
                    </div>
                ) : (
                    <ul className="space-y-2">
                        {items.map((notification) => {
                            const isUnread = notification.readAt === null;
                            const kindLabel = notificationKindLabel(
                                notification.kind,
                            );
                            const content = (
                                <>
                                    <div
                                        className={cn(
                                            'mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-2xl',
                                            isUnread
                                                ? 'bg-primary/8'
                                                : 'bg-muted/70',
                                        )}
                                    >
                                        <NotificationIcon
                                            icon={notification.icon}
                                        />
                                    </div>

                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="min-w-0 space-y-1">
                                                {kindLabel ? (
                                                    <span className="inline-flex rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground">
                                                        {kindLabel}
                                                    </span>
                                                ) : null}
                                                <p className="line-clamp-2 text-sm leading-5 font-semibold text-foreground">
                                                    {notification.title}
                                                </p>
                                            </div>

                                            {isUnread ? (
                                                <span className="mt-1 size-2.5 shrink-0 rounded-full bg-primary" />
                                            ) : null}
                                        </div>

                                        <p className="mt-1.5 line-clamp-2 text-xs leading-5 text-muted-foreground">
                                            {notification.message}
                                        </p>

                                        <div className="mt-3 flex items-center justify-between gap-3">
                                            <span className="text-[11px] font-medium text-muted-foreground">
                                                {formatNotificationTime(
                                                    notification.createdAt,
                                                )}
                                            </span>
                                            {notification.actionLabel && (
                                                <span className="inline-flex items-center gap-1 text-[11px] font-medium text-primary">
                                                    {notification.actionLabel}
                                                    <ArrowUpRight className="size-3" />
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </>
                            );

                            return (
                                <li key={notification.id}>
                                    {notification.actionUrl ? (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                handleNotificationClick(
                                                    notification,
                                                )
                                            }
                                            className={cn(
                                                'flex w-full items-start gap-3 rounded-2xl border px-4 py-3.5 text-left transition-colors duration-200 hover:border-border hover:bg-accent/30',
                                                isUnread
                                                    ? 'border-primary/15 bg-primary/6'
                                                    : 'border-border/60 bg-background/70',
                                            )}
                                        >
                                            {content}
                                        </button>
                                    ) : (
                                        <div
                                            className={cn(
                                                'flex w-full items-start gap-3 rounded-2xl border px-4 py-3.5 text-left',
                                                isUnread
                                                    ? 'border-primary/15 bg-primary/6'
                                                    : 'border-border/60 bg-background/70',
                                            )}
                                        >
                                            {content}
                                        </div>
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                )}

                <CatalogPagination
                    data={{ ...notifications, data: items }}
                    resourceName="notifikasi"
                />
            </div>
        </PageLayout>
    );
}
