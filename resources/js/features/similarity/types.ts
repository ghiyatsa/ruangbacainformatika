import { AlertCircle, CheckCircle2, TriangleAlert } from 'lucide-react';
import type { ElementType } from 'react';

export interface SimilarityItem {
    skripsi_id: number;
    student_id?: string;
    judul: string;
    nama_mahasiswa: string;
    similarity_persen: number;
    level: string;
}

export interface SimilarityResult {
    total_found: number;
    results: SimilarityItem[];
}

export interface LevelConfig {
    label: string;
    color: string;
    bg: string;
    badgeClass: string;
    trackClass: string;
    icon: ElementType<{ className?: string }>;
}

export function getLevelConfig(level: string): LevelConfig {
    const normalizedLevel = level.toUpperCase();

    switch (normalizedLevel) {
        case 'SANGAT TINGGI':
            return {
                label: 'Sangat Tinggi',
                color: 'text-rose-600 dark:text-rose-400',
                bg: 'bg-rose-500',
                badgeClass:
                    'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
                trackClass: 'bg-rose-100 dark:bg-rose-950/30',
                icon: TriangleAlert,
            };
        case 'TINGGI':
            return {
                label: 'Tinggi',
                color: 'text-orange-600 dark:text-orange-400',
                bg: 'bg-orange-500',
                badgeClass:
                    'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800',
                trackClass: 'bg-orange-100 dark:bg-orange-950/30',
                icon: AlertCircle,
            };
        case 'SEDANG':
            return {
                label: 'Sedang',
                color: 'text-amber-600 dark:text-amber-400',
                bg: 'bg-amber-500',
                badgeClass:
                    'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
                trackClass: 'bg-amber-100 dark:bg-amber-950/30',
                icon: AlertCircle,
            };
        case 'RENDAH':
            return {
                label: 'Rendah',
                color: 'text-emerald-600 dark:text-emerald-400',
                bg: 'bg-emerald-500',
                badgeClass:
                    'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
                trackClass: 'bg-emerald-100 dark:bg-emerald-950/30',
                icon: CheckCircle2,
            };
        case 'SANGAT RENDAH':
            return {
                label: 'Sangat Rendah',
                color: 'text-blue-600 dark:text-blue-400',
                bg: 'bg-blue-500',
                badgeClass:
                    'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800',
                trackClass: 'bg-blue-100 dark:bg-blue-950/30',
                icon: CheckCircle2,
            };
        default:
            return {
                label: level || 'Tidak Diketahui',
                color: 'text-muted-foreground',
                bg: 'bg-muted',
                badgeClass: 'bg-muted text-muted-foreground border-muted',
                trackClass: 'bg-muted/30',
                icon: AlertCircle,
            };
    }
}
