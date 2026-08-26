import { Head } from '@inertiajs/react';
import { LoginPage } from '@/features/auth/components/LoginPage';

export default function Login({ googleLoginUrl }: { googleLoginUrl: string }) {
    return (
        <>
            <Head title="Masuk">
                <meta
                    head-key="robots"
                    name="robots"
                    content="noindex, nofollow"
                />
            </Head>
            <LoginPage googleLoginUrl={googleLoginUrl} />
        </>
    );
}

Login.layout = {
    title: 'Masuk ke Ruang Baca',
    description: 'Silakan masuk menggunakan Akun Google Anda.',
};
