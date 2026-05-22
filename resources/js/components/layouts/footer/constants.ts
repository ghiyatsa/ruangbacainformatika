import {
    FileCheck,
    LogIn,
    Mail,
    Search,
    Terminal,
    UserPlus,
    Wrench,
} from 'lucide-react';
import {
    contact,
    home,
    login,
    privacyPolicy,
    register,
    search,
    termsOfService,
} from '@/routes';
import similarity from '@/routes/similarity';
import type { Variants } from 'motion/react';

export const INFO_LINKS = [
    {
        label: 'Universitas Malikussaleh',
        href: 'https://www.unimal.ac.id',
        external: true,
    },
    {
        label: 'Prodi Teknik Informatika',
        href: 'https://informatika.unimal.ac.id',
        external: true,
    },
    {
        label: 'Sistem Informasi Akademik',
        href: 'https://sia.unimal.ac.id',
        external: true,
    },
];

export const LEGAL_LINKS = [
    {
        label: 'Kebijakan Privasi',
        href: () => privacyPolicy.url(),
        icon: FileCheck,
        internal: true,
    },
    {
        label: 'Syarat Layanan',
        href: () => termsOfService.url(),
        icon: FileCheck,
        internal: true,
    },
    {
        label: 'Kontak',
        href: () => contact.url(),
        icon: Mail,
        internal: true,
    },
];

export const SERVICE_LINKS = [
    {
        label: 'Beranda',
        href: () => home.url(),
        icon: Terminal,
        internal: true,
    },
    {
        label: 'Cari Buku',
        href: () => search.url(),
        icon: Search,
        internal: true,
    },
    {
        label: 'Cek Kemiripan',
        href: () => similarity.index.url(),
        icon: Wrench,
        internal: true,
    },
    { label: 'Masuk', href: () => login.url(), icon: LogIn, internal: true },
    {
        label: 'Daftar',
        href: () => register.url(),
        icon: UserPlus,
        internal: true,
    },
];

export const fadeUp: Variants = {
    hidden: { opacity: 0, y: 18 },
    show: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.45,
            ease: 'easeOut' as const,
            delay: i * 0.08,
        },
    }),
};
