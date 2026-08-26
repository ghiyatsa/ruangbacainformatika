import { NotificationsPage } from '@/features/notifications/components/NotificationsPage';
import type { SiteNotification } from '@/types';
import type { PaginationData } from '@/types/pagination';

interface Props {
    notifications: PaginationData<SiteNotification>;
}

export default function NotificationIndex({ notifications }: Props) {
    return <NotificationsPage notifications={notifications} />;
}
