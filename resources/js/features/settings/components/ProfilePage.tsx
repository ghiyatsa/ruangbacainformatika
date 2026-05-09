import { Head, usePage } from '@inertiajs/react';
import { Separator } from '@/components/ui/separator';
import type { ProfileInformationFormProps } from '@/features/settings/components/profile/ProfileInformationForm';
import { ProfileInformationForm } from '@/features/settings/components/profile/ProfileInformationForm';
import { ProfileSummary } from '@/features/settings/components/profile/ProfileSummary';
import type { Auth } from '@/types';

export type ProfilePageProps = Omit<ProfileInformationFormProps, 'user'>;

export default function ProfilePage({
    mustVerifyEmail,
    status,
}: ProfilePageProps) {
    const { auth } = usePage<{ auth: Auth }>().props;

    return (
        <>
            <Head title="Pengaturan profil" />
            <h1 className="sr-only">Pengaturan profil</h1>

            <div className="flex flex-col gap-10">
                <ProfileSummary
                    name={auth.user.name}
                    email={auth.user.email}
                />

                <Separator />

                <ProfileInformationForm
                    user={auth.user}
                    mustVerifyEmail={mustVerifyEmail}
                    status={status}
                />
            </div>
        </>
    );
}
