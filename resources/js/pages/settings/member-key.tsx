import { Head, setLayoutProps } from '@inertiajs/react';
import { MemberKeySection } from '@/features/settings/components/profile/MemberKeySection';
import settings from '@/routes/settings';

interface Props {
    memberKey: {
        hasActiveQr: boolean;
        expiresAt: string | null;
        expiresAtIso: string | null;
        qrCodeSvg: string | null;
    };
}

export default function MemberKeyPage({ memberKey }: Props) {
    setLayoutProps({
        title: 'Member Key',
    });

    return (
        <>
            <Head title="Member Key" />
            <MemberKeySection memberKey={memberKey} />
        </>
    );
}

MemberKeyPage.layout = {
    breadcrumbs: [
        {
            title: 'Member Key',
            href: settings.memberKey.show(),
        },
    ],
};
