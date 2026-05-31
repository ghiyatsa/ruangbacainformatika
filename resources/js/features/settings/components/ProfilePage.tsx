import { Head, usePage } from '@inertiajs/react';
import { Separator } from '@/components/ui/separator';
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

            <div className="flex flex-col gap-10">
                <ProfileSummary
                    name={user.name}
                    email={user.email}
                    avatar={user.avatar}
                />

                <Separator />

                <ProfileInformationForm user={user} />
            </div>
        </>
    );
}
