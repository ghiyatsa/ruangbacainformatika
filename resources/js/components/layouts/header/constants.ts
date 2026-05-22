import {
    BookOpen,
    ClipboardCheck,
    GraduationCap,
    Home,
    Info,
    Mail,
    ScanSearch,
} from 'lucide-react';
import { about, contact, home } from '@/routes';
import books from '@/routes/books';
import internshipReports from '@/routes/internship-reports';
import similarity from '@/routes/similarity';
import skripsi from '@/routes/skripsi';
import thesis from '@/routes/thesis';
import type { LucideIcon } from 'lucide-react';

export interface NavLink {
    label: string;
    href: string;
    icon: LucideIcon;
    description?: string;
}

export interface NavItem {
    label: string;
    icon: LucideIcon;
    href?: string;
    children?: NavLink[];
}

export const NAV_LINKS: NavItem[] = [
    { label: 'Beranda', href: home.url(), icon: Home },
    {
        label: 'Katalog',
        icon: BookOpen,
        children: [
            {
                label: 'Buku',
                href: books.index.url(),
                description: 'Koleksi buku teks dan referensi umum.',
                icon: BookOpen,
            },
            {
                label: 'Skripsi',
                href: skripsi.index.url(),
                description: 'Koleksi tugas akhir mahasiswa Informatika.',
                icon: GraduationCap,
            },
            {
                label: 'Tesis',
                href: thesis.index.url(),
                description: 'Koleksi tesis mahasiswa magister Informatika.',
                icon: GraduationCap,
            },
            {
                label: 'Laporan KP',
                href: internshipReports.index.url(),
                description:
                    'Koleksi laporan kerja praktik mahasiswa Informatika.',
                icon: ClipboardCheck,
            },
        ],
    },
    { label: 'Cek Kemiripan', href: similarity.index.url(), icon: ScanSearch },
    { label: 'Tentang', href: about.url(), icon: Info },
    { label: 'Kontak', href: contact.url(), icon: Mail },
];
