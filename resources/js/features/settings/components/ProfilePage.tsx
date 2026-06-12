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

            <div className="space-y-6">
                <div className="rounded-xl border border-border/70 bg-card p-6 shadow-xs">
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
