import { Head } from '@inertiajs/react';
import { RegisterProfilePage } from '@/features/auth/components/RegisterProfilePage';

export default function RegisterProfile() {
    return (
        <>
            <Head title="Lengkapi Profil" />
            <RegisterProfilePage />
        </>
    );
}

RegisterProfile.layout = {
    title: 'Lengkapi profil',
    description: 'Lengkapi data akun untuk melanjutkan.',
};
