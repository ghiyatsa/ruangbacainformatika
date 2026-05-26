export type NotificationSummary = {
    unreadCount: number;
};

export type SiteNotification = {
    id: string;
    title: string;
    message: string;
    actionLabel: string | null;
    actionUrl: string | null;
    icon: string | null;
    kind: string | null;
    readAt: string | null;
    createdAt: string;
};
