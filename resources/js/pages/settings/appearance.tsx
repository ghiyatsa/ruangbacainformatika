import AppearancePage from '@/features/settings/components/AppearancePage';
import settings from '@/routes/settings';

export default function Appearance() {
    return <AppearancePage />;
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Pengaturan tampilan',
            href: settings.appearance.edit(),
        },
    ],
};
