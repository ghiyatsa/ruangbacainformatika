import { BookOpen, FileCheck, Info, Mail, Users } from 'lucide-react';
import {
    about,
    aboutTeam,
    contact,
    privacyPolicy,
    termsOfService,
} from '@/routes';
import books from '@/routes/books';

export const KOLEKSI_LINKS = [
    {
        label: 'Katalog Buku',
        href: () => books.index.url(),
        icon: BookOpen,
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
        label: 'Tentang Tim',
        href: () => aboutTeam.url(),
        icon: Users,
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
