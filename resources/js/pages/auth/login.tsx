import { Head } from '@inertiajs/react';
import { LoginPage } from '@/features/auth/components/LoginPage';

export default function Login({ googleLoginUrl }: { googleLoginUrl: string }) {
    return (
        <>
            <Head title="Masuk" />
            <LoginPage googleLoginUrl={googleLoginUrl} />
        </>
    );
}

Login.layout = {
    title: 'Masuk ke Ruang Baca',
    description: 'Silakan masuk menggunakan Akun Google Anda.',
};
