import { Head } from '@inertiajs/react';
import { VerifyWhatsAppPage } from '@/features/auth/components/VerifyWhatsAppPage';

export default function VerifyWhatsApp() {
    return (
        <>
            <Head title="Verifikasi WhatsApp" />
            <VerifyWhatsAppPage />
        </>
    );
}

VerifyWhatsApp.layout = {
    title: 'Verifikasi WhatsApp',
    description: 'Masukkan kode yang dikirim ke WhatsApp Anda.',
};
