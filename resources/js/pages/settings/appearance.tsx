import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/settings/AppearanceTabs';
import Heading from '@/components/common/Heading';
import settings from '@/routes/settings';

export default function Appearance() {
    return (
        <>
            <Head title="Appearance settings" />

            <h1 className="sr-only">Appearance settings</h1>

            <div className="flex flex-col gap-6">
                <Heading
                    variant="small"
                    title="Appearance settings"
                    description="Update your account's appearance settings"
                />
                <AppearanceTabs />
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Appearance settings',
            href: settings.appearance.edit(),
        },
    ],
};
