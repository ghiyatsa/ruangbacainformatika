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
        label: 'Kunjungan',
        description: 'Catat kehadiran',
        helper: 'Isi data singkat lalu simpan.',
        icon: ClipboardList,
    },
    {
        key: 'member',
        label: 'Anggota Baru',
        description: 'Buat akun anggota',
        helper: 'Lengkapi identitas untuk mulai memakai layanan.',
        icon: UserPlus,
    },
    {
        key: 'borrow',
        label: 'Pinjam Buku',
        description: 'Pilih dan konfirmasi',
        helper: 'Bisa scan QR atau input manual.',
        icon: BookMarked,
    },
    {
        key: 'return',
        label: 'Kembalikan Buku',
        description: 'Tandai buku selesai',
        helper: 'Cari pinjaman aktif lalu selesaikan.',
        icon: BookUp,
    },
];
