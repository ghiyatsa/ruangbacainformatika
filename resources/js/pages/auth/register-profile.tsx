import { Head } from '@inertiajs/react';
import { RegisterProfilePage } from '@/features/auth/components/RegisterProfilePage';

export default function RegisterProfile() {
    return (
        <>
            <Head title="Lengkapi Profil">
                <meta
                    head-key="robots"
                    name="robots"
                    content="noindex, nofollow"
                />
            </Head>
            <RegisterProfilePage />
        </>
    );
}

RegisterProfile.layout = {
    title: 'Lengkapi profil',
    description: 'Lengkapi data akun untuk melanjutkan.',
};
