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
        title: 'Kartu Anggota (QR)',
    });

    return (
        <>
            <Head title="Kartu Anggota (QR)" />
            <MemberKeySection memberKey={memberKey} />
        </>
    );
}

MemberKeyPage.layout = {
    breadcrumbs: [
        {
            title: 'Kartu Anggota (QR)',
            href: settings.memberKey.show(),
        },
    ],
};
