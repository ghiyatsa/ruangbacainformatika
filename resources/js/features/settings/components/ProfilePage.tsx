import { Head, usePage } from '@inertiajs/react';
import { ProfileInformationForm } from '@/features/settings/components/profile/ProfileInformationForm';
import { ProfileSummary } from '@/features/settings/components/profile/ProfileSummary';
import type { Auth } from '@/types';

export default function ProfilePage() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const user = auth.user!;

    return (
        <>
            <Head title="Pengaturan profil" />
            <h1 className="sr-only">Pengaturan profil</h1>

            <div className="space-y-10">
                <div className="pb-8 border-b border-border/60">
                    <ProfileSummary
                        name={user.name}
                        email={user.email}
                        avatar={user.avatar}
                        whatsapp={user.whatsapp}
                    />
                </div>

                <ProfileInformationForm user={user} />
            </div>
        </>
    );
}
