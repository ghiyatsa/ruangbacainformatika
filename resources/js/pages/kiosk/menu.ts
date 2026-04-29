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
        description: 'Masukkan identitas anggota dan ISBN buku.',
        icon: BookMarked,
    },
    {
        key: 'return',
        label: 'Pengembalian Buku',
        description: 'Proses buku kembali dengan identitas anggota.',
        icon: BookUp,
    },
];
