import { useForm } from '@inertiajs/react';
import {
    CheckCircle2,
    Clock3,
    QrCode,
    TriangleAlert,
} from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
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
import { FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { KioskField } from '@/features/kiosk/components/KioskField';
import type { KioskMemberRegistrationClaim } from '@/features/kiosk/types';
import { toast } from 'sonner';

interface MemberFormProps {
    memberRegistrationClaim?: KioskMemberRegistrationClaim | null;
}

const CLAIM_TIMEOUT_SECONDS = 3 * 60;
const INITIAL_FORM_DATA = {
    name: '',
    email: '',
    whatsapp: '',
    address: '',
};
const QR_SURFACE_COLOR = 'rgb(15, 23, 42)';
const QR_MODULE_COLOR = 'rgb(255, 255, 255)';

function getSecondsRemaining(expiresAt: string | null | undefined): number {
    if (!expiresAt) {
        return 0;
    }

    const expiresAtMs = new Date(expiresAt).getTime();

    if (Number.isNaN(expiresAtMs)) {
        return 0;
    }

    return Math.max(0, Math.ceil((expiresAtMs - Date.now()) / 1000));
}

function formatRemaining(seconds: number): string {
    const safeSeconds = Math.max(0, seconds);
    const minutes = Math.floor(safeSeconds / 60);
    const remainingSeconds = safeSeconds % 60;

    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds
        .toString()
        .padStart(2, '0')}`;
}

function getCsrfToken(): string | null {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? null;
}

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

export function MemberForm({ memberRegistrationClaim }: MemberFormProps) {
    const form = useForm(INITIAL_FORM_DATA);
    const [isClaimDialogOpen, setIsClaimDialogOpen] = useState(
        Boolean(memberRegistrationClaim),
    );
    const [activeRegistration, setActiveRegistration] =
        useState<KioskMemberRegistrationClaim | null>(
            memberRegistrationClaim ?? null,
        );
    const [secondsRemaining, setSecondsRemaining] = useState(() =>
        getSecondsRemaining(memberRegistrationClaim?.expiresAt),
    );
    const pollingIntervalRef = useRef<number | null>(null);
    const completionTimeoutRef = useRef<number | null>(null);
    const completionRequestRef = useRef(false);
    const handledCompletionClaimIdRef = useRef<number | null>(null);
    const isPollingRef = useRef(false);

    const isPendingClaim = activeRegistration?.status === 'pending';
    const isLinkedClaim = activeRegistration?.status === 'linked';
    const isClaimed = activeRegistration?.status === 'claimed';
    const isExpired = activeRegistration?.status === 'expired';
    const isKioskCompleted = isLinkedClaim || isClaimed;

    const isComplete =
        form.data.name.trim() !== '' &&
        form.data.email.trim() !== '' &&
        form.data.whatsapp.trim() !== '' &&
        form.data.address.trim() !== '';

    const countdownProgress = useMemo(() => {
        return Math.max(
            0,
            Math.min(100, (secondsRemaining / CLAIM_TIMEOUT_SECONDS) * 100),
        );
    }, [secondsRemaining]);

    const renderedQrSvg = useMemo(() => {
        if (!activeRegistration?.qrSvg) {
            return '';
        }

        return applyQrThemeColors(activeRegistration.qrSvg, {
            background: QR_SURFACE_COLOR,
            foreground: QR_MODULE_COLOR,
        });
    }, [activeRegistration?.qrSvg]);

    const resetMemberForm = () => {
        form.reset();
        form.clearErrors();
        form.setData(INITIAL_FORM_DATA);
    };

    const clearCompletionTimeout = () => {
        if (completionTimeoutRef.current) {
            window.clearTimeout(completionTimeoutRef.current);
            completionTimeoutRef.current = null;
        }
    };

    const submitMemberRegistrationRequest = () => {
        form.post(KioskController.storeMember.url(), {
            preserveScroll: true,
            onSuccess: () => {
                setIsClaimDialogOpen(true);
                clearCompletionTimeout();
                completionRequestRef.current = false;
                handledCompletionClaimIdRef.current = null;
            },
        });
    };

    const finalizeActiveRegistration = async (options: {
        mode: 'cancel' | 'dismiss' | 'restart';
        resetForm?: boolean;
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
            setSecondsRemaining(0);
            setIsClaimDialogOpen(false);
            setActiveRegistration(null);

            if (options.resetForm ?? true) {
                resetMemberForm();
            }

            if (options.mode === 'cancel') {
                toast.info('Proses dibatalkan.');
            }

            if (options.mode === 'dismiss') {
                toast.success('Akun Google berhasil ditautkan.');
            }

            if (options.mode === 'restart') {
                toast.info('QR kedaluwarsa. Membuat QR baru.');
                submitMemberRegistrationRequest();
            }
        }
    };

    useEffect(() => {
        setActiveRegistration(memberRegistrationClaim ?? null);
    }, [memberRegistrationClaim]);

    useEffect(() => {
        if (
            memberRegistrationClaim &&
            memberRegistrationClaim.status !== 'claimed' &&
            memberRegistrationClaim.status !== 'linked'
        ) {
            setIsClaimDialogOpen(true);
        }
    }, [memberRegistrationClaim]);

    useEffect(() => {
        if (!isClaimDialogOpen || (!isPendingClaim && !isLinkedClaim)) {
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
                        current?.status === 'claimed' ||
                        current?.status === 'expired'
                    ) {
                        return current;
                    }

                    return payload.memberRegistrationClaim;
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
    }, [isClaimDialogOpen, isLinkedClaim, isPendingClaim]);

    useEffect(() => {
        if (!isClaimDialogOpen || !isPendingClaim) {
            setSecondsRemaining(getSecondsRemaining(activeRegistration?.expiresAt));

            return;
        }

        const tick = () => {
            setSecondsRemaining(getSecondsRemaining(activeRegistration?.expiresAt));
        };

        tick();

        const interval = window.setInterval(tick, 1000);

        return () => window.clearInterval(interval);
    }, [activeRegistration?.expiresAt, isClaimDialogOpen, isPendingClaim]);

    useEffect(() => {
        if (!activeRegistration || !isKioskCompleted) {
            return;
        }

        if (handledCompletionClaimIdRef.current === activeRegistration.id) {
            return;
        }

        handledCompletionClaimIdRef.current = activeRegistration.id;
        resetMemberForm();
        setSecondsRemaining(0);
        clearCompletionTimeout();

        completionTimeoutRef.current = window.setTimeout(() => {
            void finalizeActiveRegistration({
                mode: 'dismiss',
            });
        }, 900);

        return () => {
            clearCompletionTimeout();
        };
    }, [activeRegistration, isKioskCompleted]);

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

    const handleDialogOpenChange = (open: boolean) => {
        setIsClaimDialogOpen(open);

        if (!open && activeRegistration && !isPendingClaim && !isLinkedClaim) {
            setActiveRegistration(null);
        }
    };

    const cancelRegistration = () => {
        void finalizeActiveRegistration({
            mode: 'cancel',
        });
    };

    const restartRegistration = () => {
        if (!isComplete || form.processing) {
            return;
        }

        void finalizeActiveRegistration({
            mode: 'restart',
            resetForm: false,
        });
    };

    const submitMemberRegistration = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        submitMemberRegistrationRequest();
    };

    return (
        <div className="flex flex-col gap-6">
            {activeRegistration && !isClaimDialogOpen ? (
                <div className="rounded-3xl border border-border/70 bg-muted/20 p-5">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="space-y-2">
                                <Badge variant="outline">
                                {isPendingClaim
                                    ? 'Proses masih berjalan'
                                    : isLinkedClaim
                                      ? 'Registrasi selesai'
                                    : isExpired
                                      ? 'Waktu habis'
                                      : 'Registrasi berhasil'}
                            </Badge>
                            <div className="space-y-1">
                                <p className="text-base font-semibold text-foreground">
                                    Registrasi {activeRegistration.name}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {isPendingClaim
                        ? 'QR masih aktif.'
                        : isLinkedClaim
                          ? 'Akun sudah tertaut.'
                        : isExpired
                          ? 'QR sudah kedaluwarsa.'
                          : 'Form siap digunakan lagi.'}
                                </p>
                            </div>
                        </div>

                        <Button
                            type="button"
                            variant={isPendingClaim ? 'default' : 'secondary'}
                            onClick={() => setIsClaimDialogOpen(true)}
                        >
                            {isPendingClaim ? <QrCode className="size-4" /> : null}
                            {isPendingClaim ? 'Buka QR' : 'Lihat status'}
                        </Button>
                    </div>
                </div>
            ) : null}

            <form
                onSubmit={submitMemberRegistration}
                className="flex flex-col gap-4"
                autoComplete="off"
            >
                <input
                    type="text"
                    name="username"
                    autoComplete="username"
                    tabIndex={-1}
                    className="hidden"
                    aria-hidden="true"
                />
                <input
                    type="password"
                    name="password"
                    autoComplete="current-password"
                    tabIndex={-1}
                    className="hidden"
                    aria-hidden="true"
                />
                <FieldGroup className="grid gap-4 sm:grid-cols-2">
                    <KioskField
                        label="Nama Lengkap"
                        htmlFor="reg-name"
                        error={form.errors.name}
                        required
                    >
                        <input type="hidden" name="name" value={form.data.name} />
                        <Input
                            id="reg-name"
                            autoFocus
                            autoComplete="new-password"
                            autoCapitalize="words"
                            autoCorrect="off"
                            spellCheck={false}
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true"
                            placeholder="Nama lengkap"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            aria-invalid={Boolean(form.errors.name)}
                            disabled={form.processing || isPendingClaim || isLinkedClaim}
                        />
                    </KioskField>

                    <KioskField
                        label="Email UNIMAL"
                        htmlFor="reg-email"
                        error={form.errors.email}
                        required
                    >
                        <input
                            type="hidden"
                            name="email"
                            value={form.data.email}
                        />
                        <Input
                            id="reg-email"
                            type="email"
                            autoComplete="new-password"
                            autoCapitalize="none"
                            autoCorrect="off"
                            spellCheck={false}
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true"
                            placeholder="nama@mhs.unimal.ac.id"
                            value={form.data.email}
                            onChange={(event) =>
                                form.setData('email', event.target.value)
                            }
                            aria-invalid={Boolean(form.errors.email)}
                            disabled={form.processing || isPendingClaim || isLinkedClaim}
                        />
                    </KioskField>

                    <KioskField
                        label="No. WhatsApp"
                        htmlFor="reg-whatsapp"
                        error={form.errors.whatsapp}
                        required
                    >
                        <input
                            type="hidden"
                            name="whatsapp"
                            value={form.data.whatsapp}
                        />
                        <Input
                            id="reg-whatsapp"
                            type="tel"
                            autoComplete="new-password"
                            autoCapitalize="none"
                            autoCorrect="off"
                            spellCheck={false}
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true"
                            placeholder="08123456789"
                            value={form.data.whatsapp}
                            onChange={(event) =>
                                form.setData('whatsapp', event.target.value)
                            }
                            aria-invalid={Boolean(form.errors.whatsapp)}
                            disabled={form.processing || isPendingClaim || isLinkedClaim}
                        />
                    </KioskField>

                    <KioskField
                        label="Alamat"
                        htmlFor="reg-address"
                        error={form.errors.address}
                        required
                        className="sm:col-span-2"
                    >
                        <input
                            type="hidden"
                            name="address"
                            value={form.data.address}
                        />
                        <Textarea
                            id="reg-address"
                            autoComplete="new-password"
                            autoCorrect="off"
                            spellCheck={false}
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true"
                            placeholder="Masukkan alamat lengkap"
                            value={form.data.address}
                            onChange={(event) =>
                                form.setData('address', event.target.value)
                            }
                            aria-invalid={Boolean(form.errors.address)}
                            className="min-h-28 resize-y"
                            disabled={form.processing || isPendingClaim || isLinkedClaim}
                        />
                    </KioskField>
                </FieldGroup>

                <Button
                    type="submit"
                    size="lg"
                    disabled={
                        form.processing || !isComplete || isPendingClaim || isLinkedClaim
                    }
                >
                    {form.processing ? <Spinner /> : <QrCode className="size-4" />}
                    {form.processing ? 'Menyiapkan QR...' : 'Tampilkan QR'}
                </Button>
                <p className="text-sm text-muted-foreground">
                    Gunakan akun Google dengan email yang sama.
                </p>
            </form>

            <Dialog open={isClaimDialogOpen} onOpenChange={handleDialogOpenChange}>
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

                    {activeRegistration ? (
                        <div className="overflow-hidden rounded-[2rem] bg-white text-slate-950 shadow-2xl ring-1 ring-black/10 dark:bg-slate-950 dark:text-slate-50 dark:ring-white/10">
                            <div className="bg-[radial-gradient(circle_at_top,_rgba(5,150,105,0.16),_transparent_56%),linear-gradient(180deg,#f8fffc_0%,#ffffff_42%)] px-6 py-6 dark:bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.16),_transparent_56%),linear-gradient(180deg,#07130f_0%,#0f172a_42%)] sm:px-8">
                                <div className="mx-auto flex max-w-md flex-col items-center gap-5 text-center">
                                    <Badge
                                        variant="outline"
                                        className="rounded-full border border-emerald-300/80 bg-white/90 px-3 py-1 text-xs font-medium uppercase tracking-[0.22em] text-emerald-700 dark:border-emerald-500/40 dark:bg-slate-950/70 dark:text-emerald-300"
                                    >
                                        {isPendingClaim
                                            ? 'Menunggu scan'
                                            : isLinkedClaim
                                              ? 'Selesai'
                                              : isClaimed
                                                ? 'Berhasil'
                                                : 'Kedaluwarsa'}
                                    </Badge>

                                    <div className="space-y-1.5">
                                        <h2 className="text-2xl font-semibold tracking-tight sm:text-3xl">
                                            {isClaimed
                                                ? 'Akun tertaut'
                                                : isLinkedClaim
                                                  ? 'Registrasi selesai'
                                                : isExpired
                                                  ? 'Waktu habis'
                                                  : 'Scan QR'}
                                        </h2>
                                        <p className="mx-auto max-w-sm text-sm leading-6 text-slate-600 dark:text-slate-300">
                                            {isClaimed
                                                ? activeRegistration.approvalPending
                                                    ? 'Akun menunggu persetujuan admin.'
                                                    : 'Selesai.'
                                                : isLinkedClaim
                                                  ? 'Selesai.'
                                                : isExpired
                                                  ? 'QR kedaluwarsa. Buat ulang untuk lanjut.'
                                                  : 'Pilih akun Google yang sesuai.'}
                                        </p>
                                    </div>

                                    {isPendingClaim ? (
                                        <div className="rounded-[1.75rem] border border-slate-800 bg-slate-950 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:p-5">
                                            <div
                                                className="flex justify-center [&_svg]:mx-auto [&_svg]:block [&_svg]:h-[240px] [&_svg]:w-[240px] sm:[&_svg]:h-[280px] sm:[&_svg]:w-[280px]"
                                                dangerouslySetInnerHTML={{
                                                    __html: renderedQrSvg,
                                                }}
                                            />
                                        </div>
                                    ) : (
                                        <div className="w-full max-w-md rounded-[1.75rem] border border-slate-200 bg-white px-6 py-7 text-left dark:border-slate-800 dark:bg-slate-900">
                                            <div className="flex items-start gap-3">
                                                <Clock3 className="mt-0.5 size-5 text-emerald-600 dark:text-emerald-400" />
                                                <div className="space-y-2">
                                                    <p className="text-sm font-medium text-slate-900 dark:text-slate-100">
                                                        {isLinkedClaim
                                                            ? 'Akun sudah tertaut.'
                                                            : activeRegistration.approvalPending
                                                              ? 'Proses selesai.'
                                                              : 'Proses hampir selesai.'}
                                                    </p>
                                                    <p className="text-sm leading-6 text-slate-600 dark:text-slate-300">
                                                        {isLinkedClaim
                                                            ? 'Form sedang diatur ulang.'
                                                            : activeRegistration.approvalPending
                                                              ? 'Akses peminjaman menunggu persetujuan admin.'
                                                              : 'Form sedang diatur ulang.'}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    )}

                                    {isPendingClaim ? (
                                        <div className="w-full max-w-sm space-y-3">
                                            <div className="flex items-center justify-center gap-2 text-emerald-700 dark:text-emerald-300">
                                                <Clock3 className="size-4" />
                                                <span className="text-sm font-medium">
                                                    Scan sekarang
                                                </span>
                                            </div>
                                            <div className="text-4xl font-semibold tracking-[0.18em] tabular-nums sm:text-5xl">
                                                {formatRemaining(secondsRemaining)}
                                            </div>
                                            <div className="h-2 overflow-hidden rounded-full bg-emerald-100 dark:bg-emerald-950/60">
                                                <div
                                                    className="h-full rounded-full bg-emerald-600 transition-[width] dark:bg-emerald-400"
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

                                    {isClaimed ? (
                                        <Alert className="w-full max-w-md border-emerald-200 bg-emerald-50 text-left text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                                            <CheckCircle2 className="size-4" />
                                            <AlertTitle>
                                                {activeRegistration.approvalPending
                                                    ? 'Menunggu persetujuan admin'
                                                    : 'Akun sudah terhubung'}
                                            </AlertTitle>
                                            <AlertDescription>
                                                {activeRegistration.approvalPending
                                                    ? 'Akses peminjaman akan aktif setelah disetujui admin.'
                                                    : 'Form sedang diatur ulang.'}
                                            </AlertDescription>
                                        </Alert>
                                    ) : null}
                                </div>
                            </div>

                            {!isClaimed ? (
                                <div className="flex flex-col gap-2 border-t border-slate-200/80 bg-white/80 px-5 py-4 dark:border-slate-800 dark:bg-slate-950/80 sm:flex-row sm:justify-end">
                                    {isExpired ? (
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            onClick={restartRegistration}
                                            disabled={!isComplete || form.processing}
                                        >
                                            Buat QR Baru
                                        </Button>
                                    ) : (
                                        <>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={cancelRegistration}
                                            >
                                                Batalkan proses
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                onClick={() =>
                                                    handleDialogOpenChange(false)
                                                }
                                            >
                                                Tutup
                                            </Button>
                                        </>
                                    )}
                                </div>
                            ) : null}
                        </div>
                    ) : (
                        <div className="rounded-[2rem] bg-white p-6 text-slate-950 shadow-2xl ring-1 ring-black/10 dark:bg-slate-950 dark:text-slate-50 dark:ring-white/10">
                            <Alert className="dark:border-slate-800 dark:bg-slate-900">
                                <TriangleAlert className="size-4" />
                                <AlertTitle>Proses sudah ditutup</AlertTitle>
                                <AlertDescription>
                                    Buat QR baru jika masih diperlukan.
                                </AlertDescription>
                            </Alert>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}
