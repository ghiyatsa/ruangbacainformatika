import { useEffect } from 'react';
import { PageLayout } from '@/components/layout/PageLayout';
import { MemberKeySection } from '@/features/settings/components/profile/MemberKeySection';
import { setPageBreadcrumbs } from '@/layouts/AppLayout';
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
    useEffect(() => {
        setPageBreadcrumbs([
            { title: 'Beranda', href: '/' },
            {
                title: 'Kartu Anggota (QR)',
                href: settings.memberKey.show.url(),
            },
        ]);

        return () => {
            setPageBreadcrumbs(undefined);
        };
    }, []);

    return (
        <PageLayout
            title="Kartu Anggota (QR)"
            description="Tunjukkan kode QR ke pemindai layar untuk transaksi peminjaman mandiri."
            maxWidth="3xl"
            showDesktopNoticeInContent={false}
            className="pt-8 pb-16"
        >
            <MemberKeySection memberKey={memberKey} />
        </PageLayout>
    );
}
