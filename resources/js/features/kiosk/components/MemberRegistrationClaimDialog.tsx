import { CheckCircle2, Clock3, TriangleAlert } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useCountdown } from '@/hooks/use-countdown';
import { getCsrfToken } from '@/lib/csrf';
import { formatCountdown } from '@/lib/format-countdown';
import type { KioskMemberRegistrationClaim } from '@/features/kiosk/types';

interface MemberRegistrationClaimDialogProps {
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
    initialRegistration: KioskMemberRegistrationClaim;
    onLinked: () => void;
    onRestart: () => void;
    onCancel: () => void;
}

const CLAIM_TIMEOUT_SECONDS = 3 * 60;
const QR_SURFACE_COLOR = 'var(--card)';
const QR_MODULE_COLOR = 'var(--foreground)';

function applyQrThemeColors(
    svg: string,
    colors: { background: string; foreground: string },
): string {
    return svg
        .replaceAll('var(--background)', colors.background)
        .replaceAll('var(--foreground)', colors.foreground)
        .replaceAll('currentColor', colors.foreground)
        .replaceAll('fill="none"', 'fill="transparent"');
}

export function MemberRegistrationClaimDialog({
    isOpen,
    onOpenChange,
    initialRegistration,
    onLinked,
    onRestart,
    onCancel,
}: MemberRegistrationClaimDialogProps) {
    const [activeRegistration, setActiveRegistration] =
        useState<KioskMemberRegistrationClaim>(initialRegistration);

    const pollingIntervalRef = useRef<number | null>(null);
    const completionTimeoutRef = useRef<number | null>(null);
    const completionRequestRef = useRef(false);
    const handledCompletionClaimIdRef = useRef<number | null>(null);
    const isPollingRef = useRef(false);

    const isPendingClaim = activeRegistration.status === 'pending';
    const isLinkedClaim = activeRegistration.status === 'linked';
    const isClaimed = activeRegistration.status === 'claimed';
    const isExpired = activeRegistration.status === 'expired';
    const isKioskCompleted = isLinkedClaim || isClaimed;
    const isConnectionCompleted = isKioskCompleted;
    // Only run the countdown while the claim is actually pending.
    const { remainingSeconds } = useCountdown(
        isPendingClaim ? activeRegistration.expiresAt : null,
    );
    const secondsRemaining = remainingSeconds ?? 0;

    const countdownProgress = useMemo(() => {
        return Math.max(
            0,
            Math.min(100, (secondsRemaining / CLAIM_TIMEOUT_SECONDS) * 100),
        );
    }, [secondsRemaining]);

    const isLowTime = secondsRemaining < 60;
    const timerColorClass = isLowTime
        ? 'text-red-600 dark:text-red-400 animate-pulse'
        : 'text-slate-900 dark:text-slate-100';
    const timerIconColorClass = isLowTime
        ? 'text-red-600 dark:text-red-400'
        : 'text-primary dark:text-primary';
    const progressBarColorClass = isLowTime
        ? 'bg-red-600 dark:bg-red-500'
        : 'bg-primary';
    const progressBgColorClass = isLowTime
        ? 'bg-red-100 dark:bg-red-950/60'
        : 'bg-primary/10 dark:bg-primary/20';
    const bracketColorClass = isLowTime ? 'border-red-500' : 'border-primary';

    // Flat color background used instead of gradient

    const renderedQrSvg = useMemo(() => {
        if (!activeRegistration.qrSvg) {
            return '';
        }

        return applyQrThemeColors(activeRegistration.qrSvg, {
            background: QR_SURFACE_COLOR,
            foreground: QR_MODULE_COLOR,
        });
    }, [activeRegistration.qrSvg]);

    const clearCompletionTimeout = () => {
        if (completionTimeoutRef.current) {
            window.clearTimeout(completionTimeoutRef.current);
            completionTimeoutRef.current = null;
        }
    };

    const finalizeActiveRegistration = useCallback(
        async (options: {
            mode: 'cancel' | 'dismiss' | 'restart';
        }): Promise<void> => {
            if (options.mode === 'dismiss' && completionRequestRef.current) {
                return;
            }

            if (options.mode === 'dismiss') {
                completionRequestRef.current = true;
            }

            try {
                const csrfToken = getCsrfToken();

                await fetch(KioskController.cancelMemberRegistration.url(), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                });
            } finally {
                clearCompletionTimeout();
                handledCompletionClaimIdRef.current = null;
                completionRequestRef.current = false;
                onOpenChange(false);

                if (options.mode === 'cancel') {
                    onCancel();
                    toast.info('Proses dibatalkan.');
                }

                if (options.mode === 'dismiss') {
                    onLinked();
                    toast.success('Akun Google berhasil ditautkan.');
                }

                if (options.mode === 'restart') {
                    onRestart();
                    toast.info('QR kedaluwarsa. Membuat QR baru.');
                }
            }
        },
        [onCancel, onLinked, onOpenChange, onRestart],
    );

    // Update state when initialRegistration changes from parent
    useEffect(() => {
        window.setTimeout(() => {
            setActiveRegistration(initialRegistration);
        }, 0);
    }, [initialRegistration]);

    // Poll registration status
    useEffect(() => {
        if (!isOpen || !isPendingClaim) {
            if (pollingIntervalRef.current) {
                window.clearInterval(pollingIntervalRef.current);
                pollingIntervalRef.current = null;
            }

            return;
        }

        const pollStatus = async () => {
            if (isPollingRef.current) {
                return;
            }

            isPollingRef.current = true;

            try {
                const response = await fetch(
                    KioskController.memberRegistrationStatus.url(),
                    {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    },
                );

                if (!response.ok) {
                    return;
                }

                const payload = (await response.json()) as {
                    memberRegistrationClaim: KioskMemberRegistrationClaim | null;
                };

                setActiveRegistration((current) => {
                    if (payload.memberRegistrationClaim) {
                        return payload.memberRegistrationClaim;
                    }

                    if (
                        current.status === 'linked' ||
                        current.status === 'claimed' ||
                        current.status === 'expired'
                    ) {
                        return current;
                    }

                    return {
                        ...current,
                        status: 'expired',
                    };
                });
            } finally {
                isPollingRef.current = false;
            }
        };

        void pollStatus();
        pollingIntervalRef.current = window.setInterval(() => {
            void pollStatus();
        }, 2000);

        return () => {
            if (pollingIntervalRef.current) {
                window.clearInterval(pollingIntervalRef.current);
                pollingIntervalRef.current = null;
            }

            isPollingRef.current = false;
        };
    }, [isOpen, isPendingClaim]);

    // Finalize registration upon completion (linked or claimed)
    useEffect(() => {
        if (!isKioskCompleted) {
            return;
        }

        if (handledCompletionClaimIdRef.current === activeRegistration.id) {
            return;
        }

        handledCompletionClaimIdRef.current = activeRegistration.id;
        clearCompletionTimeout();

        completionTimeoutRef.current = window.setTimeout(() => {
            void finalizeActiveRegistration({
                mode: 'dismiss',
            });
        }, 900);

        return () => {
            clearCompletionTimeout();
        };
    }, [activeRegistration, finalizeActiveRegistration, isKioskCompleted]);

    // Cleanup timers on unmount
    useEffect(() => {
        return () => {
            if (pollingIntervalRef.current) {
                window.clearInterval(pollingIntervalRef.current);
            }

            if (completionTimeoutRef.current) {
                window.clearTimeout(completionTimeoutRef.current);
            }

            isPollingRef.current = false;
        };
    }, []);

    const cancelRegistration = () => {
        void finalizeActiveRegistration({
            mode: 'cancel',
        });
    };

    const restartRegistration = () => {
        void finalizeActiveRegistration({
            mode: 'restart',
        });
    };

    return (
        <Dialog open={isOpen} onOpenChange={onOpenChange}>
            <DialogContent
                className="max-w-xl border-none bg-transparent p-0 shadow-none"
                showCloseButton={false}
            >
                <DialogHeader className="sr-only">
                    <DialogTitle>QR tautkan akun</DialogTitle>
                    <DialogDescription>
                        Scan QR untuk menautkan akun Google.
                    </DialogDescription>
                </DialogHeader>

                <div className="overflow-hidden rounded-xl bg-card text-card-foreground shadow-2xl ring-1 ring-foreground/10">
                    <div className="bg-card px-5 py-4 sm:px-6 sm:py-5">
                        <div className="mx-auto flex max-w-md flex-col items-center gap-3.5 text-center">
                            <Badge
                                variant="outline"
                                className={
                                    isPendingClaim
                                        ? 'rounded-full border border-primary/30 bg-primary/5 px-3 py-1 text-xs font-medium tracking-[0.22em] text-primary uppercase dark:border-primary/40 dark:bg-primary/10 dark:text-primary'
                                        : isConnectionCompleted
                                          ? 'rounded-full border border-emerald-300/80 bg-emerald-50/50 px-3 py-1 text-xs font-medium tracking-[0.22em] text-emerald-800 uppercase dark:border-emerald-500/40 dark:bg-slate-950/70 dark:text-emerald-300'
                                          : 'rounded-full border border-red-300/80 bg-red-50/50 px-3 py-1 text-xs font-medium tracking-[0.22em] text-red-800 uppercase dark:border-red-500/40 dark:bg-slate-950/70 dark:text-red-300'
                                }
                            >
                                {isPendingClaim
                                    ? 'Menunggu scan'
                                    : isConnectionCompleted
                                      ? 'Terhubung'
                                      : 'Kedaluwarsa'}
                            </Badge>

                            <div className="space-y-2">
                                <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                    {isConnectionCompleted
                                        ? 'Pendaftaran Berhasil!'
                                        : isExpired
                                          ? 'Sesi Telah Berakhir'
                                          : 'Tautkan Akun Google'}
                                </h2>
                                <p className="mx-auto max-w-sm text-sm leading-relaxed text-muted-foreground">
                                    {isConnectionCompleted
                                        ? 'Akun Google Anda telah terhubung dengan data pendaftaran di perpustakaan.'
                                        : isExpired
                                          ? 'QR code pendaftaran telah kedaluwarsa demi keamanan data Anda.'
                                          : 'Pindai QR ini dengan ponsel Anda untuk masuk menggunakan Google dan menyelesaikan pendaftaran.'}
                                </p>
                            </div>

                            {isPendingClaim ? (
                                <div className="relative overflow-hidden rounded-2xl bg-card p-3.5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] sm:p-4">
                                    {/* Corner Brackets */}
                                    <div
                                        className={`absolute top-1 left-1 size-4 border-t-2 border-l-2 ${bracketColorClass} rounded-tl-md`}
                                    />
                                    <div
                                        className={`absolute top-1 right-1 size-4 border-t-2 border-r-2 ${bracketColorClass} rounded-tr-md`}
                                    />
                                    <div
                                        className={`absolute bottom-1 left-1 size-4 border-b-2 border-l-2 ${bracketColorClass} rounded-bl-md`}
                                    />
                                    <div
                                        className={`absolute right-1 bottom-1 size-4 border-r-2 border-b-2 ${bracketColorClass} rounded-br-md`}
                                    />

                                    <div
                                        className="flex justify-center [&_svg]:mx-auto [&_svg]:block [&_svg]:h-[180px] [&_svg]:w-[180px] sm:[&_svg]:h-[220px] sm:[&_svg]:w-[220px]"
                                        dangerouslySetInnerHTML={{
                                            __html: renderedQrSvg,
                                        }}
                                    />
                                </div>
                            ) : isConnectionCompleted ? (
                                <div className="w-full max-w-md animate-in rounded-[1.75rem] border border-emerald-100 bg-emerald-50/20 px-6 py-7 text-left duration-300 fade-in dark:border-emerald-900/30 dark:bg-emerald-950/10">
                                    <div className="flex items-start gap-3">
                                        <CheckCircle2 className="mt-0.5 size-5 text-emerald-600 dark:text-emerald-400" />
                                        <div className="space-y-1">
                                            <p className="text-sm font-semibold text-emerald-900 dark:text-emerald-400">
                                                Akun Google berhasil ditautkan!
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            ) : (
                                <div className="w-full max-w-md animate-in rounded-[1.75rem] border border-red-100 bg-red-50/20 px-6 py-7 text-left duration-300 fade-in dark:border-red-900/30 dark:bg-red-950/10">
                                    <div className="flex items-start gap-3">
                                        <TriangleAlert className="mt-0.5 size-5 text-red-600 dark:text-red-400" />
                                        <div className="space-y-1">
                                            <p className="text-sm font-semibold text-red-900 dark:text-red-300">
                                                QR Telah Kedaluwarsa
                                            </p>
                                            <p className="text-sm leading-relaxed text-red-800 dark:text-red-300">
                                                Batas waktu penautan akun (3
                                                menit) telah habis. Silakan klik
                                                tombol "Buat QR Baru" di bawah
                                                untuk mencoba kembali.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {isPendingClaim ? (
                                <div className="w-full max-w-sm space-y-2">
                                    <div
                                        className={`flex items-center justify-center gap-2 ${timerIconColorClass}`}
                                    >
                                        <Clock3 className="size-4" />
                                        <span className="text-sm font-medium">
                                            Scan sekarang
                                        </span>
                                    </div>
                                    <div
                                        className={`text-4xl font-semibold tracking-[0.18em] tabular-nums sm:text-5xl ${timerColorClass}`}
                                    >
                                        {formatCountdown(secondsRemaining)}
                                    </div>
                                    <div
                                        className={`h-2 overflow-hidden rounded-full ${progressBgColorClass}`}
                                    >
                                        <div
                                            className={`h-full rounded-full transition-[width] ${progressBarColorClass}`}
                                            style={{
                                                width: `${countdownProgress}%`,
                                            }}
                                        />
                                    </div>
                                </div>
                            ) : null}

                            {activeRegistration.lastErrorMessage ? (
                                <Alert
                                    variant="destructive"
                                    className="w-full max-w-lg text-left"
                                >
                                    <TriangleAlert className="size-4" />
                                    <AlertTitle>
                                        Penautan belum berhasil
                                    </AlertTitle>
                                    <AlertDescription>
                                        {activeRegistration.lastErrorMessage}
                                    </AlertDescription>
                                </Alert>
                            ) : null}

                            {isConnectionCompleted ? (
                                <Alert className="w-full max-w-md border-emerald-200 bg-emerald-50 text-left text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                                    <CheckCircle2 className="size-4" />
                                    <AlertTitle>
                                        Akun sudah terhubung
                                    </AlertTitle>
                                    <AlertDescription>
                                        Form sedang diatur ulang.
                                    </AlertDescription>
                                </Alert>
                            ) : null}
                        </div>
                    </div>

                    {!isConnectionCompleted ? (
                        <div className="flex flex-col gap-2 border-t border-border bg-card px-5 py-3 sm:flex-row sm:justify-end">
                            {isExpired ? (
                                <Button
                                    type="button"
                                    variant="secondary"
                                    onClick={restartRegistration}
                                    className="rounded-xl"
                                >
                                    Buat QR Baru
                                </Button>
                            ) : (
                                <>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={cancelRegistration}
                                        className="rounded-xl"
                                    >
                                        Batalkan
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        onClick={() => onOpenChange(false)}
                                        className="rounded-xl"
                                    >
                                        Tutup
                                    </Button>
                                </>
                            )}
                        </div>
                    ) : null}
                </div>
            </DialogContent>
        </Dialog>
    );
}
