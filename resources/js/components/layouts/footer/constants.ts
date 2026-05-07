import {
    BookOpen,
    BookText,
    LogIn,
    Search,
    Terminal,
    UserPlus,
    Wrench,
} from 'lucide-react';
import type { Variants } from 'motion/react';
import { home, login, register, search } from '@/routes';
import books from '@/routes/books';
import similarity from '@/routes/similarity';

export const CATALOG_LINKS = [
    { label: 'Semua Buku', href: () => books.index.url(), icon: BookOpen },
    {
        label: 'Tersedia Dipinjam',
        href: () => `${books.index.url()}?filter=available`,
        icon: BookText,
    },
    {
        label: 'Koleksi Unggulan',
        href: () => `${books.index.url()}?filter=featured`,
        icon: BookText,
    },
];

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
