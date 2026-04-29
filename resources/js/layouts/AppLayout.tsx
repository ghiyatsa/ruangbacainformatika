import { TooltipProvider } from '@/components/ui/tooltip';
import AppLayoutTemplate from '@/layouts/AppHeaderLayout';
import type { BreadcrumbItem } from '@/types';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    return (
        <TooltipProvider>
            <AppLayoutTemplate breadcrumbs={breadcrumbs}>
                {children}
            </AppLayoutTemplate>
        </TooltipProvider>
    );
}
