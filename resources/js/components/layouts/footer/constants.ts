import {
    BookOpen,
    ClipboardCheck,
    FileCheck,
    GraduationCap,
    Info,
    LogIn,
    Mail,
    UserPlus,
} from 'lucide-react';
import { about, contact, privacyPolicy, termsOfService } from '@/routes';
import books from '@/routes/books';
import internshipReports from '@/routes/internship-reports';
import skripsi from '@/routes/skripsi';
import thesis from '@/routes/thesis';
import type { Variants } from 'motion/react';

export const KOLEKSI_LINKS = [
    {
        label: 'Buku',
        href: () => books.index.url(),
        icon: BookOpen,
        internal: true,
    },
    {
        label: 'Skripsi',
        href: () => skripsi.index.url(),
        icon: GraduationCap,
        internal: true,
    },
    {
        label: 'Tesis',
        href: () => thesis.index.url(),
        icon: GraduationCap,
        internal: true,
    },
    {
        label: 'Laporan KP',
        href: () => internshipReports.index.url(),
        icon: ClipboardCheck,
        internal: true,
    },
];

export const LEGAL_LINKS = [
    {
        label: 'Tentang',
        href: () => about.url(),
        icon: Info,
        internal: true,
    },
    {
        label: 'Kontak',
        href: () => contact.url(),
        icon: Mail,
        internal: true,
    },
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
];

export const GUEST_SERVICE_LINKS = [
    { label: 'Masuk', hrefKey: 'login', icon: LogIn, internal: true },
    {
        label: 'Daftar',
        hrefKey: 'register',
        icon: UserPlus,
        internal: true,
    },
] as const;

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
