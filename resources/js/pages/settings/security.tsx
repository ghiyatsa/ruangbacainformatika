import { Head } from '@inertiajs/react';
import SecurityPage from '@/features/settings/components/SecurityPage';
import type { SecurityPageProps } from '@/features/settings/types';
import settings from '@/routes/settings';

export default function Security(props: SecurityPageProps) {
    return (
        <>
            <Head title="Pengaturan keamanan" />
            <h1 className="sr-only">Pengaturan keamanan</h1>
            <SecurityPage {...props} />
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'Pengaturan keamanan',
            href: settings.security.edit(),
        },
    ],
};
