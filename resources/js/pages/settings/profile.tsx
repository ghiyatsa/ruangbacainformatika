import { setLayoutProps } from '@inertiajs/react';
import ProfilePage from '@/features/settings/components/ProfilePage';
import type { ProfilePageProps } from '@/features/settings/components/ProfilePage';
import settings from '@/routes/settings';

export default function Profile(props: ProfilePageProps) {
    setLayoutProps({
        title: 'Pengaturan profil',
        description: 'Kelola nama, nomor WhatsApp, dan informasi akun Anda.',
    });

    return <ProfilePage {...props} />;
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Pengaturan profil',
            href: settings.profile.edit(),
        },
    ],
};
