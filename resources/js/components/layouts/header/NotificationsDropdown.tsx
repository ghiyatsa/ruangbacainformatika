import { router } from '@inertiajs/react';
import { Bell, BellRing, BookCheck, CheckCheck } from 'lucide-react';
import * as React from 'react';
import * as NotificationController from '@/actions/App/Http/Controllers/NotificationController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogDescription,
    DialogContent,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';
import type { SiteNotification } from '@/types';

interface NotificationsDropdownProps {
    initialUnreadCount: number;
}

type NotificationsResponse = {
    notifications: SiteNotification[];
    unreadCount: number;
};

function getCsrfToken(): string | null {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? null
    );
}

function formatNotificationTime(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return 'Baru saja';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

function NotificationIcon({ icon }: { icon: string | null }) {
    if (icon === 'bell-ring') {
        return <BellRing className="size-4 text-amber-600" />;
    }

    return <BookCheck className="size-4 text-emerald-600" />;
}

export function NotificationsDropdown({
    initialUnreadCount,
}: NotificationsDropdownProps) {
    const isMobile = useIsMobile();
    const [notifications, setNotifications] = React.useState<
        SiteNotification[]
    >([]);
    const [open, setOpen] = React.useState(false);
    const [isLoading, setIsLoading] = React.useState(false);
    const [unreadCount, setUnreadCount] = React.useState(initialUnreadCount);

    const readNotification = async (notificationId: string) => {
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

        return (await response.json()) as { unreadCount: number };
    };

    const loadNotifications = React.useEffectEvent(async () => {
        setIsLoading(true);

        try {
            const response = await fetch(NotificationController.index.url(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = (await response.json()) as NotificationsResponse;

            setNotifications(payload.notifications);
            setUnreadCount(payload.unreadCount);
        } finally {
            setIsLoading(false);
        }
    });

    const markAllAsRead = React.useEffectEvent(async () => {
        if (unreadCount === 0) {
            return;
        }

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

        setUnreadCount(0);
        setNotifications((currentNotifications) =>
            currentNotifications.map((notification) => ({
                ...notification,
                readAt: notification.readAt ?? new Date().toISOString(),
            })),
        );
    });

    const handleOpenChange = React.useEffectEvent((nextOpen: boolean) => {
        setOpen(nextOpen);

        if (nextOpen) {
            void loadNotifications();
        }
    });

    const handleNotificationClick = React.useEffectEvent(
        async (notification: SiteNotification) => {
            if (notification.readAt === null) {
                const payload = await readNotification(notification.id);

                if (payload) {
                    setUnreadCount(payload.unreadCount);
                    setNotifications((currentNotifications) =>
                        currentNotifications.map((currentNotification) =>
                            currentNotification.id === notification.id
                                ? {
                                      ...currentNotification,
                                      readAt: new Date().toISOString(),
                                  }
                                : currentNotification,
                        ),
                    );
                }
            }

            if (notification.actionUrl) {
                setOpen(false);
                router.visit(notification.actionUrl);
            }
        },
    );

    const trigger = (
        <Button
            variant="ghost"
            size="icon"
            className="group relative h-9 w-9 rounded-xl transition-all duration-300 hover:scale-105 active:scale-95"
            aria-label={`Notifikasi, ${unreadCount} belum dibaca`}
            title="Notifikasi"
        >
            <Bell className="h-[18px] w-[18px] text-primary transition-transform duration-300 group-hover:scale-110" />
            <span className="sr-only">Notifikasi</span>
            {unreadCount > 0 && (
                <Badge className="absolute top-0.5 right-0.5 flex h-3 min-w-3 animate-in items-center justify-center rounded-full px-1 py-0 text-[8px] leading-none shadow-sm duration-200 zoom-in-50">
                    {unreadCount > 9 ? '9+' : unreadCount}
                </Badge>
            )}
        </Button>
    );

    const mobileHeader = (
        <>
            <div className="flex items-center justify-between border-b border-border/60 px-4 py-3">
                <div>
                    <DialogTitle className="text-sm font-semibold">
                        Notifikasi
                    </DialogTitle>
                    <DialogDescription className="sr-only">
                        Daftar notifikasi terbaru untuk akunmu.
                    </DialogDescription>
                </div>

                {unreadCount > 0 && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="h-8 rounded-lg px-2 text-xs"
                        onClick={() => void markAllAsRead()}
                        disabled={unreadCount === 0}
                    >
                        <CheckCheck className="mr-1 size-3.5" />
                        Tandai dibaca
                    </Button>
                )}
            </div>
        </>
    );

    const desktopHeader = (
        <div className="flex items-center justify-between border-b border-border/60 px-4 py-3">
            <div>
                <h2 className="text-sm font-semibold">Notifikasi</h2>
            </div>

            {unreadCount > 0 && (
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="h-8 rounded-lg px-2 text-xs"
                    onClick={() => void markAllAsRead()}
                    disabled={unreadCount === 0}
                >
                    <CheckCheck className="mr-1 size-3.5" />
                    Tandai dibaca
                </Button>
            )}
        </div>
    );

    const contentBody = (
        <>
            <div
                className={`max-h-96 overflow-y-auto ${open ? 'motion-safe:animate-in motion-safe:fade-in-0 motion-safe:slide-in-from-top-1 motion-safe:duration-200' : ''}`}
            >
                {isLoading ? (
                    <div className="space-y-3 px-4 py-4 motion-safe:animate-in motion-safe:fade-in-0 motion-safe:duration-150">
                        {[0, 1, 2].map((item) => (
                            <div
                                key={item}
                                className="animate-pulse rounded-2xl border border-border/50 p-3"
                            >
                                <div className="h-3 w-24 rounded bg-muted" />
                                <div className="mt-2 h-3 w-full rounded bg-muted" />
                                <div className="mt-1 h-3 w-2/3 rounded bg-muted" />
                            </div>
                        ))}
                    </div>
                ) : notifications.length === 0 ? (
                    <div className="px-4 py-8 text-center motion-safe:animate-in motion-safe:fade-in-0 motion-safe:zoom-in-95 motion-safe:duration-200">
                        <div className="mx-auto mb-3 flex size-11 items-center justify-center rounded-full bg-primary/8 text-primary">
                            <Bell className="size-5" />
                        </div>
                        <p className="text-sm font-medium">
                            Belum ada notifikasi
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Notifikasi buku dan aktivitas akun akan muncul di
                            sini.
                        </p>
                    </div>
                ) : (
                    <div className="p-2">
                        {notifications.map((notification, index) => {
                            const isUnread = notification.readAt === null;

                            return (
                                <button
                                    key={notification.id}
                                    type="button"
                                    className={cn(
                                        'flex w-full items-start gap-3 rounded-2xl px-3 py-3 text-left transition-[background-color,transform,opacity] duration-200 hover:bg-accent/60',
                                        open &&
                                            'motion-safe:animate-in motion-safe:fade-in-0 motion-safe:slide-in-from-top-1',
                                        isUnread && 'bg-primary/6',
                                    )}
                                    style={
                                        open
                                            ? {
                                                  animationDuration: '220ms',
                                                  animationDelay: `${Math.min(index * 35, 140)}ms`,
                                                  animationFillMode:
                                                      'backwards',
                                              }
                                            : undefined
                                    }
                                    onClick={() =>
                                        void handleNotificationClick(
                                            notification,
                                        )
                                    }
                                >
                                    <div className="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full bg-muted/70">
                                        <NotificationIcon
                                            icon={notification.icon}
                                        />
                                    </div>

                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-start gap-2">
                                            <p className="line-clamp-1 text-sm font-medium">
                                                {notification.title}
                                            </p>
                                            {isUnread && (
                                                <span className="mt-1 size-2 shrink-0 rounded-full bg-primary" />
                                            )}
                                        </div>
                                        <p className="mt-1 line-clamp-2 text-xs leading-5 text-muted-foreground">
                                            {notification.message}
                                        </p>
                                        <div className="mt-2 flex items-center justify-between gap-3">
                                            <span className="text-[11px] text-muted-foreground">
                                                {formatNotificationTime(
                                                    notification.createdAt,
                                                )}
                                            </span>
                                            {notification.actionLabel && (
                                                <span className="text-[11px] font-medium text-primary">
                                                    {notification.actionLabel}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                )}
            </div>
        </>
    );

    if (isMobile) {
        return (
            <Dialog open={open} onOpenChange={handleOpenChange}>
                <DialogTrigger asChild>
                    {trigger}
                </DialogTrigger>

                <DialogContent
                    className="w-[min(92vw,22rem)] max-w-[min(92vw,22rem)] gap-0 overflow-hidden p-0"
                    showCloseButton={false}
                >
                    {mobileHeader}
                    {contentBody}
                </DialogContent>
            </Dialog>
        );
    }

    return (
        <DropdownMenu open={open} onOpenChange={handleOpenChange}>
            <DropdownMenuTrigger asChild>
                {trigger}
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" className="w-80 min-w-80 p-0">
                {desktopHeader}
                {contentBody}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
