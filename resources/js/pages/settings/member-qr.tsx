import { Head, useForm } from '@inertiajs/react';
import { Download, QrCode, ShieldCheck, TimerReset } from 'lucide-react';
import { useEffect, useState } from 'react';
import MemberQrController from '@/actions/App/Http/Controllers/Settings/MemberQrController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { downloadSvgAsPng } from '@/lib/utils';
import settings from '@/routes/settings';
import type { FormEvent } from 'react';

interface Props {
    memberQr: {
        hasActiveQr: boolean;
        expiresAt: string | null;
        expiresAtIso: string | null;
        qrCodeSvg: string | null;
    };
}

function formatCountdown(totalSeconds: number): string {
    if (totalSeconds <= 0) {
        return '00:00';
    }

    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return [minutes, seconds]
        .map((value) => String(value).padStart(2, '0'))
        .join(':');
}

export default function MemberQrPage({ memberQr }: Props) {
    const form = useForm({});
    const [currentTimestamp, setCurrentTimestamp] = useState(() => Date.now());
    const expiresAtTimestamp = memberQr.expiresAtIso
        ? new Date(memberQr.expiresAtIso).getTime()
        : null;
    const remainingSeconds =
        expiresAtTimestamp === null
            ? null
            : Math.max(
                  Math.ceil((expiresAtTimestamp - currentTimestamp) / 1000),
                  0,
              );
    const countdownLabel =
        remainingSeconds === null ? null : formatCountdown(remainingSeconds);
    const hasActiveQrCountdown =
        memberQr.hasActiveQr &&
        remainingSeconds !== null &&
        remainingSeconds > 0;

    useEffect(() => {
        if (expiresAtTimestamp === null) {
            return;
        }

        const interval = window.setInterval(() => {
            setCurrentTimestamp(Date.now());
        }, 1000);

        return () => window.clearInterval(interval);
    }, [expiresAtTimestamp]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(MemberQrController.generate.url(), {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title="QR Anggota" />

            <div className="flex flex-col gap-6">
                <section className="rounded-xl border border-border/70 bg-card p-6 shadow-xs">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 className="text-base font-semibold">
                                QR Anggota
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Gunakan QR ini saat meminjam buku di kiosk untuk
                                memverifikasi identitas akun Anda.
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <Badge
                                variant={
                                    hasActiveQrCountdown ? 'success' : 'outline'
                                }
                            >
                                {hasActiveQrCountdown
                                    ? 'QR sedang aktif'
                                    : 'QR belum aktif'}
                            </Badge>
                            <Badge variant="outline">
                                Berlaku singkat untuk satu sesi
                            </Badge>
                        </div>
                    </div>
                </section>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
                    <Card className="border-border/70">
                        <CardHeader>
                            <CardTitle>QR untuk peminjaman</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-5">
                            <div className="rounded-[1.75rem] border border-border/70 bg-card px-5 py-6 shadow-sm">
                                {memberQr.qrCodeSvg && countdownLabel ? (
                                    <div className="mb-5 text-center">
                                        <p className="text-[11px] font-semibold tracking-[0.2em] text-muted-foreground uppercase">
                                            Berlaku dalam
                                        </p>
                                        <p
                                            className="mt-2 text-4xl font-semibold tracking-tight text-foreground tabular-nums"
                                            title={
                                                memberQr.expiresAt
                                                    ? `Berlaku sampai ${memberQr.expiresAt}`
                                                    : undefined
                                            }
                                        >
                                            {countdownLabel}
                                        </p>
                                    </div>
                                ) : null}

                                {memberQr.qrCodeSvg ? (
                                    <div className="space-y-4">
                                        <div className="mx-auto w-max rounded-3xl border border-border/50 bg-white p-4 text-primary shadow-sm dark:bg-card dark:text-white">
                                            <div
                                                className="flex justify-center [&_svg]:mx-auto"
                                                dangerouslySetInnerHTML={{
                                                    __html: memberQr.qrCodeSvg,
                                                }}
                                            />
                                        </div>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            className="mx-auto flex items-center gap-2"
                                            onClick={() =>
                                                downloadSvgAsPng(
                                                    memberQr.qrCodeSvg!,
                                                    'qr-anggota.png',
                                                    'QR ANGGOTA',
                                                )
                                            }
                                        >
                                            <Download className="size-4" />
                                            Unduh QR
                                        </Button>
                                    </div>
                                ) : (
                                    <div className="rounded-2xl bg-muted/20 px-5 py-10 text-center text-sm text-muted-foreground">
                                        QR anggota siap dibuat.
                                    </div>
                                )}
                            </div>

                            <form onSubmit={submit}>
                                <Button
                                    type="submit"
                                    size="lg"
                                    className="w-full"
                                    disabled={
                                        form.processing || hasActiveQrCountdown
                                    }
                                >
                                    <QrCode className="size-4" />
                                    Buat QR Anggota
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <Card className="border-border/70">
                        <CardHeader>
                            <CardTitle>Status sesi</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="rounded-lg border border-border/70 bg-muted/20 p-4">
                                <div className="mb-2 flex items-center gap-2 font-medium">
                                    <TimerReset className="h-4 w-4 text-muted-foreground" />
                                    Masa berlaku
                                </div>
                                <p className="text-muted-foreground">
                                    {memberQr.expiresAt
                                        ? memberQr.expiresAt
                                        : 'QR akan aktif setelah dibuat.'}
                                </p>
                            </div>

                            <div className="rounded-lg border border-border/70 bg-muted/20 p-4">
                                <div className="mb-2 flex items-center gap-2 font-medium">
                                    <ShieldCheck className="h-4 w-4 text-muted-foreground" />
                                    Catatan
                                </div>
                                <p className="text-muted-foreground">
                                    Buat QR saat sudah siap meminjam, lalu scan
                                    di kiosk sebelum waktunya habis.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

MemberQrPage.layout = {
    breadcrumbs: [
        {
            title: 'QR anggota',
            href: settings.memberQr.show(),
        },
    ],
};
