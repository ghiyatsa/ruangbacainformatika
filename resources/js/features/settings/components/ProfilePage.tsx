import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

import { PageLayout } from '@/components/layout/PageLayout';
import { AccountSecurityCard } from '@/features/settings/components/profile/AccountSecurityCard';
import { ProfileInformationForm } from '@/features/settings/components/profile/ProfileInformationForm';
import { ProfileSummary } from '@/features/settings/components/profile/ProfileSummary';
import { setPageBreadcrumbs } from '@/layouts/AppLayout';
import settings from '@/routes/settings';
import type { VerificationProps } from '@/features/settings/components/profile/ManageWhatsAppDialog';
import type { Auth } from '@/types';

interface ProfilePageProps extends Record<string, unknown> {
    auth: Auth;
    verification?: VerificationProps | null;
}

export default function ProfilePage() {
    const { auth, verification } = usePage<ProfilePageProps>().props;
    const user = auth.user!;

    useEffect(() => {
        setPageBreadcrumbs([
            { title: 'Beranda', href: '/' },
            {
                title: 'Pengaturan Profil',
                href: settings.profile.edit.url(),
            },
        ]);

        return () => {
            setPageBreadcrumbs(undefined);
        };
    }, []);

    return (
        <PageLayout
            title="Pengaturan Profil"
            description="Kelola informasi data diri, kontak, dan keamanan akun Anda."
            maxWidth="4xl"
            showDesktopNoticeInContent={false}
            className="pt-8 pb-16"
        >
            <div className="space-y-8">
                <div className="relative overflow-hidden rounded-2xl border border-border/60 bg-linear-to-br from-card via-card to-primary/5 p-6 shadow-xs">
                    <div className="pointer-events-none absolute -right-10 -bottom-10 -z-0 h-32 w-32 rounded-full bg-primary/10 blur-2xl" />
                    <ProfileSummary
                        name={user.name}
                        email={user.email}
                        avatar={user.avatar}
                        whatsapp={user.whatsapp}
                    />
                </div>

                <div className="relative overflow-hidden rounded-2xl border border-border/60 bg-linear-to-br from-card via-card to-primary/5 p-6 shadow-xs">
                    <div className="pointer-events-none absolute -right-10 -bottom-10 -z-0 h-32 w-32 rounded-full bg-primary/10 blur-2xl" />
                    <ProfileInformationForm user={user} />
                </div>

                <div className="relative overflow-hidden rounded-2xl border border-border/60 bg-linear-to-br from-card via-card to-primary/5 p-6 shadow-xs">
                    <div className="pointer-events-none absolute -right-10 -bottom-10 -z-0 h-32 w-32 rounded-full bg-primary/10 blur-2xl" />
                    <AccountSecurityCard
                        user={user}
                        verification={verification}
                    />
                </div>
            </div>
        </PageLayout>
    );
}
