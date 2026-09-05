import { BookMarked, BookUp, ClipboardList, UserPlus } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { KioskMenu } from './types';

export interface KioskMenuItem {
    key: Exclude<KioskMenu, 'landing'>;
    label: string;
    description: string;
    helper: string;
    icon: LucideIcon;
}

export const kioskMenuItems: KioskMenuItem[] = [
    {
        key: 'visit',
        label: 'Buku Tamu',
        description: 'Catat kunjungan',
        helper: 'Isi data singkat untuk mencatat kehadiran Anda di Ruang Baca.',
        icon: ClipboardList,
    },
    {
        key: 'member',
        label: 'Daftar Anggota',
        description: 'Pendaftaran anggota baru',
        helper: 'Lengkapi data identitas, lalu tautkan dengan akun Google Anda.',
        icon: UserPlus,
    },
    {
        key: 'borrow',
        label: 'Pinjam Buku',
        description: 'Peminjaman mandiri',
        helper: 'Masukkan identitas anggota, pilih buku fisik, lalu konfirmasi dengan scan Member Key di HP.',
        icon: BookMarked,
    },
    {
        key: 'return',
        label: 'Kembalikan Buku',
        description: 'Pengembalian buku',
        helper: 'Masukkan identitas anggota, pilih buku yang dikembalikan, lalu konfirmasi dengan scan Member Key di HP.',
        icon: BookUp,
    },
];
