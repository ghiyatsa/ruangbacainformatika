import { usePage } from '@inertiajs/react';
import AuthCardLayout from '@/layouts/AuthCardLayout';
import AuthSimpleLayout from '@/layouts/AuthSimpleLayout';

export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: React.ReactNode;
}) {
    const { component } = usePage();
    const LayoutTemplate =
        component === 'auth/login' || component === 'auth/register'
            ? AuthCardLayout
            : AuthSimpleLayout;

    return (
        <LayoutTemplate title={title} description={description}>
            {children}
        </LayoutTemplate>
    );
}
