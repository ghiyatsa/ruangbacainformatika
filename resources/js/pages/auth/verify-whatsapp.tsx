import { Head } from '@inertiajs/react';
import { VerifyWhatsAppPage } from '@/features/auth/components/VerifyWhatsAppPage';

export default function VerifyWhatsApp() {
    return (
        <>
            <Head title="Verifikasi WhatsApp">
                <meta
                    head-key="robots"
                    name="robots"
                    content="noindex, nofollow"
                />
            </Head>
            <VerifyWhatsAppPage />
        </>
    );
}

VerifyWhatsApp.layout = {
    title: 'Verifikasi WhatsApp',
    description: 'Masukkan kode yang dikirim ke WhatsApp Anda.',
};
