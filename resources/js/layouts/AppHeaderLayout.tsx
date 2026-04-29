import { AppContent } from '@/components/layouts/AppContent';
import { AppHeader } from '@/components/layouts/AppHeader';
import { AppShell } from '@/components/layouts/AppShell';
import type { AppLayoutProps } from '@/types';

export default function AppHeaderLayout({ children }: AppLayoutProps) {
    return (
        <AppShell variant="header">
            <AppHeader />
            <AppContent variant="header">{children}</AppContent>
        </AppShell>
    );
}
