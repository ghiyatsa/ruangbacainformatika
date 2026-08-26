import { BellRing, BookCheck } from 'lucide-react';

export function formatNotificationTime(value: string): string {
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

export function NotificationIcon({ icon }: { icon: string | null }) {
    if (icon === 'bell-ring') {
        return (
            <BellRing className="size-4 text-amber-600 dark:text-amber-400" />
        );
    }

    return (
        <BookCheck className="size-4 text-emerald-600 dark:text-emerald-400" />
    );
}

export function notificationKindLabel(kind: string | null): string | null {
    if (kind === 'loan_receipt') {
        return 'Peminjaman';
    }

    if (kind === 'loan_reminder') {
        return 'Pengingat';
    }

    if (kind === 'post_approved' || kind === 'post_rejected') {
        return 'Artikel';
    }

    return null;
}
