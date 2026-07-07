import { Head, usePage } from '@inertiajs/react';
import { MemberKeySection } from '@/features/settings/components/profile/MemberKeySection';
import { ProfileInformationForm } from '@/features/settings/components/profile/ProfileInformationForm';
import { ProfileSummary } from '@/features/settings/components/profile/ProfileSummary';
import type { Auth } from '@/types';

interface Props {
    memberKey?: {
        hasActiveQr: boolean;
        expiresAt: string | null;
        expiresAtIso: string | null;
        qrCodeSvg: string | null;
    } | null;
}

export default function ProfilePage({ memberKey }: Props) {
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

                <div className={memberKey ? 'grid gap-8 lg:grid-cols-[1fr_360px]' : 'space-y-10'}>
                    <div className="space-y-10">
                        <ProfileInformationForm user={user} />
                    </div>

                    {memberKey && (
                        <div className="space-y-6">
                            <MemberKeySection memberKey={memberKey} />
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
