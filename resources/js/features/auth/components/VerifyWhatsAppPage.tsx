import { Form, Link, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    CheckCircle2,
    Clock3,
    Edit3,
    KeyRound,
    Phone,
    RotateCcw,
    ShieldCheck,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import WhatsAppVerificationController from '@/actions/App/Http/Controllers/Auth/WhatsAppVerificationController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatCountdown } from '@/lib/format-countdown';
import { logout } from '@/routes';

interface VerificationProps {
    maskedWhatsapp: string | null;
    hasActiveChallenge: boolean;
    expiresIn: number;
    resendAvailableIn: number;
    approvalMode: 'automatic' | 'manual';
    approvalMessage: string;
}

export function VerifyWhatsAppPage() {
    const { verification, auth, isChangingNumber } = usePage<{
        verification: VerificationProps;
        auth: {
            user: { whatsapp: string | null } | null;
            hasVerifiedWhatsApp: boolean;
        };
        isChangingNumber?: boolean;
    }>().props;

    const [currentTimestamp, setCurrentTimestamp] = useState(() => Date.now());
    const [whatsappVal, setWhatsappVal] = useState(auth.user?.whatsapp ?? '');
    const [isEditingNumber, setIsEditingNumber] = useState(
        !verification.hasActiveChallenge && !auth.user?.whatsapp,
    );

    const [countdownBase, setCountdownBase] = useState(() => ({
        expiresIn: verification.expiresIn,
        resendAvailableIn: verification.resendAvailableIn,
        startedAt: currentTimestamp,
    }));

    useEffect(() => {
        const resetCountdownTimeout = window.setTimeout(() => {
            setCountdownBase({
                expiresIn: verification.expiresIn,
                resendAvailableIn: verification.resendAvailableIn,
                startedAt: Date.now(),
            });
        }, 0);

        return () => window.clearTimeout(resetCountdownTimeout);
    }, [verification.expiresIn, verification.resendAvailableIn]);

    const elapsedSeconds = Math.max(
        0,
        Math.floor((currentTimestamp - countdownBase.startedAt) / 1000),
    );

    const resendAvailableIn = Math.max(
        0,
        countdownBase.resendAvailableIn - elapsedSeconds,
    );

    useEffect(() => {
        const interval = window.setInterval(() => {
            setCurrentTimestamp(Date.now());
        }, 1000);

        return () => window.clearInterval(interval);
    }, []);

    // Tampilkan tampilan input OTP jika challenge aktif dan user tidak sedang mengklik tombol edit nomor
    const showOtpInput = verification.hasActiveChallenge && !isEditingNumber;

    return (
        <div className="rounded-2xl border border-border/70 bg-card/70 p-6 shadow-sm">
            {/* Header */}
            <div className="mb-6 flex flex-col items-center text-center">
                <div className="mb-3 inline-flex size-11 items-center justify-center rounded-full bg-primary/10 text-primary">
                    {isChangingNumber ? (
                        <Phone className="size-5" />
                    ) : (
                        <ShieldCheck className="size-5" />
                    )}
                </div>
                <h2 className="text-lg font-semibold tracking-tight text-foreground">
                    {isChangingNumber
                        ? 'Ubah Nomor WhatsApp'
                        : 'Verifikasi Nomor WhatsApp'}
                </h2>
            </div>

            <div className="flex flex-col gap-6">
                {!showOtpInput ? (
                    /* STEP 1: Masukkan / Ganti Nomor WhatsApp */
                    <Form
                        action={WhatsAppVerificationController.send.url()}
                        method="post"
                        options={{
                            preserveScroll: true,
                        }}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="whatsapp">
                                        {isChangingNumber
                                            ? 'Nomor WhatsApp Baru'
                                            : 'Nomor WhatsApp Aktif'}
                                    </Label>
                                    <InputGroup>
                                        <InputGroupInput
                                            id="whatsapp"
                                            name="whatsapp"
                                            type="tel"
                                            value={whatsappVal}
                                            onChange={(e) =>
                                                setWhatsappVal(e.target.value)
                                            }
                                            autoComplete="tel"
                                            inputMode="tel"
                                            placeholder="Contoh: 08123456789"
                                            autoFocus
                                            required
                                        />
                                        <InputGroupAddon>
                                            <Phone className="size-4" />
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <InputError
                                        message={errors.whatsapp ?? errors.otp}
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    size="lg"
                                    disabled={
                                        processing ||
                                        (auth.hasVerifiedWhatsApp &&
                                            whatsappVal === auth.user?.whatsapp)
                                    }
                                >
                                    {processing ? (
                                        <Spinner />
                                    ) : (
                                        <KeyRound className="size-4" />
                                    )}
                                    Kirim Kode OTP
                                </Button>

                                {verification.hasActiveChallenge ? (
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            setIsEditingNumber(false)
                                        }
                                        className="text-xs text-muted-foreground"
                                    >
                                        Kembali ke pengisian kode OTP
                                    </Button>
                                ) : null}
                            </>
                        )}
                    </Form>
                ) : (
                    /* STEP 2: Masukkan Kode OTP yang Terkirim */
                    <div className="flex flex-col gap-6">
                        {/* Info banner nomor tujuan */}
                        <div className="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-3 text-sm">
                            <div className="flex items-center gap-2">
                                <Phone className="size-4 text-primary" />
                                <p className="font-semibold text-foreground">
                                    {verification.maskedWhatsapp || whatsappVal}
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() => setIsEditingNumber(true)}
                                className="h-8 gap-1.5 text-xs text-primary hover:text-primary/80"
                            >
                                <Edit3 className="size-3.5" />
                                Ganti nomor
                            </Button>
                        </div>

                        <Form
                            action={WhatsAppVerificationController.verify.url()}
                            method="post"
                            options={{ preserveScroll: true }}
                            className="flex flex-col gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <div className="flex items-center justify-between">
                                            <Label htmlFor="code">
                                                6 Digit Kode OTP
                                            </Label>
                                            {resendAvailableIn > 0 ? (
                                                <span className="flex items-center gap-1 text-xs text-muted-foreground tabular-nums">
                                                    <Clock3 className="size-3" />
                                                    Kirim ulang dalam{' '}
                                                    {formatCountdown(
                                                        resendAvailableIn,
                                                    )}
                                                </span>
                                            ) : null}
                                        </div>

                                        <InputGroup>
                                            <InputGroupInput
                                                id="code"
                                                name="code"
                                                type="text"
                                                inputMode="numeric"
                                                autoComplete="one-time-code"
                                                autoFocus
                                                maxLength={6}
                                                required
                                                className="text-center font-mono text-lg tracking-[0.3em]"
                                                placeholder="••••••"
                                            />
                                            <InputGroupAddon>
                                                <KeyRound className="size-4" />
                                            </InputGroupAddon>
                                        </InputGroup>
                                        <InputError
                                            message={errors.code ?? errors.otp}
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        className="w-full"
                                        size="lg"
                                        disabled={processing}
                                    >
                                        {processing ? (
                                            <Spinner />
                                        ) : (
                                            <CheckCircle2 className="size-4" />
                                        )}
                                        Verifikasi OTP
                                    </Button>
                                </>
                            )}
                        </Form>

                        {/* Tombol Kirim Ulang OTP */}
                        <Form
                            action={WhatsAppVerificationController.send.url()}
                            method="post"
                            options={{ preserveScroll: true }}
                            className="flex justify-center"
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    variant="ghost"
                                    size="sm"
                                    disabled={
                                        processing || resendAvailableIn > 0
                                    }
                                    className="gap-1.5 text-xs text-muted-foreground hover:text-foreground"
                                >
                                    {processing ? (
                                        <Spinner />
                                    ) : (
                                        <RotateCcw className="size-3.5" />
                                    )}
                                    {resendAvailableIn > 0
                                        ? `Kirim ulang (${formatCountdown(resendAvailableIn)})`
                                        : 'Tidak menerima kode? Kirim ulang'}
                                </Button>
                            )}
                        </Form>
                    </div>
                )}
            </div>

            {/* Footer Navigation / Skip Options */}
            <div className="mt-8 flex flex-col items-center gap-3 border-t border-border/50 pt-5 text-center">
                {isChangingNumber ? (
                    <Form
                        action="/register/whatsapp/cancel-change"
                        method="post"
                    >
                        {({ processing }) => (
                            <Button
                                type="submit"
                                variant="outline"
                                size="sm"
                                disabled={processing}
                                className="gap-2 text-sm"
                            >
                                <ArrowLeft className="size-4" />
                                Batal & Kembali ke Pengaturan
                            </Button>
                        )}
                    </Form>
                ) : (
                    <>
                        <Link
                            href={WhatsAppVerificationController.skip.url()}
                            method="post"
                            as="button"
                            className="text-sm font-medium text-muted-foreground underline underline-offset-4 transition-colors hover:text-foreground"
                        >
                            Lewati untuk sekarang
                        </Link>
                        <Link
                            href={logout().url}
                            method="post"
                            as="button"
                            className="text-xs text-muted-foreground underline underline-offset-4 transition-colors hover:text-primary"
                        >
                            Bukan akun Anda? Keluar
                        </Link>
                    </>
                )}
            </div>
        </div>
    );
}
