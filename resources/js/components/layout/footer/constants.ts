import {
    BookOpen,
    ClipboardCheck,
    FileCheck,
    GraduationCap,
    Info,
    Mail,
    Search,
    Users,
} from 'lucide-react';
import {
    about,
    aboutTeam,
    contact,
    privacyPolicy,
    search,
    termsOfService,
} from '@/routes';
import books from '@/routes/books';
import internshipReports from '@/routes/internship-reports';
import skripsi from '@/routes/skripsi';
import thesis from '@/routes/thesis';

export const KOLEKSI_LINKS = [
    {
        label: 'Pencarian',
        href: () => search.url(),
        icon: Search,
        internal: true,
    },
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
