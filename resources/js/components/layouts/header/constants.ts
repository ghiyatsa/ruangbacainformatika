import type {
    LucideIcon} from 'lucide-react';
import {
    BookOpen,
    ClipboardCheck,
    GraduationCap,
    Home,
    Info,
    Mail,
    ScanSearch,
} from 'lucide-react';

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
    { label: 'Beranda', href: '/', icon: Home },
    {
        label: 'Katalog',
        icon: BookOpen,
        children: [
            {
                label: 'Buku',
                href: '/books',
                description: 'Koleksi buku teks dan referensi umum.',
                icon: BookOpen,
            },
            {
                label: 'Skripsi',
                href: '/skripsi',
                description: 'Koleksi tugas akhir mahasiswa Informatika.',
                icon: GraduationCap,
            },
            {
                label: 'Laporan KP',
                href: '/internship-reports',
                description: 'Koleksi laporan kerja praktik mahasiswa Informatika.',
                icon: ClipboardCheck,
            },
        ],
    },
    { label: 'Similarity', href: '/similarity', icon: ScanSearch },
    { label: 'Tentang', href: '/about', icon: Info },
    { label: 'Kontak', href: '/contact', icon: Mail },
];
