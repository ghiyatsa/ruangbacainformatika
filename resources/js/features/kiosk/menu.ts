import { BookMarked, BookUp, ClipboardList, UserPlus } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { KioskMenu } from './types';

export interface KioskMenuItem {
    key: Exclude<KioskMenu, 'landing'>;
    label: string;
    description: string;
    icon: LucideIcon;
}

export const kioskMenuItems: KioskMenuItem[] = [
    {
        key: 'visit',
        label: 'Daftar Kunjungan',
        description: 'Catat kedatangan pengunjung perpustakaan.',
        icon: ClipboardList,
    },
    {
        key: 'member',
        label: 'Registrasi Member',
        description: 'Buat akun anggota seperti pendaftaran manual.',
        icon: UserPlus,
    },
    {
        key: 'borrow',
        label: 'Peminjaman Buku',
        description: 'Cari buku yang tersedia lalu proses pinjaman anggota.',
        icon: BookMarked,
    },
    {
        key: 'return',
        label: 'Pengembalian Buku',
        description: 'Cari buku pinjaman aktif milik anggota untuk dikembalikan.',
        icon: BookUp,
    },
];
