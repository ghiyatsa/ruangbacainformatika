import { useEffect, useMemo, useState } from 'react';
import { RuangBacaLogo } from '@/components/common/RuangBacaLogo';
import { Badge } from '@/components/ui/badge';
import {
    KioskStatsGrid,
    KioskTimePanel
    
} from '@/features/kiosk/components/KioskAttractCards';
import { PROGRAM_NAME, UNIVERSITY_NAME } from '@/lib/brand';
import type {StatItem} from '@/features/kiosk/components/KioskAttractCards';
import type { KioskInfo, KioskSessionConfig } from '@/features/kiosk/types';

function greetingForHour(hour: number): string {
    if (hour < 11) {
        return 'Selamat Pagi';
    }

    if (hour < 15) {
        return 'Selamat Siang';
    }

    if (hour < 18) {
        return 'Selamat Sore';
    }

    return 'Selamat Malam';
}

export interface KioskAttractScreenProps {
    session: KioskSessionConfig;
    info?: KioskInfo;
}

export function KioskAttractScreen({
    session,
    info,
}: KioskAttractScreenProps) {
    const [currentTime, setCurrentTime] = useState(() => new Date());

    useEffect(() => {
        const timer = window.setInterval(() => {
            setCurrentTime(new Date());
        }, 1000);

        return () => {
            window.clearInterval(timer);
        };
    }, []);

    const formattedDate = useMemo(
        () =>
            new Intl.DateTimeFormat('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }).format(currentTime),
        [currentTime],
    );

    const stats: StatItem[] = useMemo(
        () => [
            {
                label: 'Kunjungan',
                value: info?.todayVisits ?? 0,
                bgHover: 'hover:bg-blue-500/5',
            },
            {
                label: 'Peminjaman',
                value: info?.todayBorrowed ?? 0,
                bgHover: 'hover:bg-emerald-500/5',
            },
            {
                label: 'Pengembalian',
                value: info?.todayReturned ?? 0,
                bgHover: 'hover:bg-amber-500/5',
            },
        ],
        [info?.todayVisits, info?.todayBorrowed, info?.todayReturned],
    );

    return (
        <div className="flex h-full min-h-0 flex-col justify-between gap-6">
            <div className="flex justify-end">
                <Badge
                    variant="outline"
                    className={`px-3 py-1 text-xs ${
                        session.withinOperatingHours
                            ? 'border-emerald-500/30 text-emerald-600 dark:text-emerald-400'
                            : 'border-amber-500/30 text-amber-600 dark:text-amber-400'
                    }`}
                >
                    {session.withinOperatingHours ? 'Buka' : 'Di Luar Jam'}{' '}
                    {session.operatingOpenTime}–{session.operatingCloseTime}
                </Badge>
            </div>

            <div className="flex flex-col items-center justify-center space-y-4">
                <RuangBacaLogo className="size-16 text-primary" />

                <div className="space-y-1.5 text-center">
                    <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        {greetingForHour(currentTime.getHours())}, Silakan Mulai
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        Pilih salah satu layanan mandiri di sebelah kiri untuk
                        memulai transaksi.
                    </p>
                    <p className="text-xs text-muted-foreground/80">
                        {PROGRAM_NAME} {UNIVERSITY_NAME}
                    </p>
                </div>

                <div className="pt-2">
                    <KioskTimePanel
                        currentTime={currentTime}
                        formattedDate={formattedDate}
                    />
                </div>
            </div>

            <div className="space-y-2 pt-4">
                <div className="text-center text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">
                    Statistik Hari Ini
                </div>
                <KioskStatsGrid stats={stats} />
            </div>
        </div>
    );
}
