import { Form, Head, Link, usePage } from '@inertiajs/react';
import { CheckCircle2, Clock3 } from 'lucide-react';
import { useEffect, useState } from 'react';
import WhatsAppVerificationController from '@/actions/App/Http/Controllers/Auth/WhatsAppVerificationController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';

interface VerificationProps {
    maskedWhatsapp: string | null;
    hasActiveChallenge: boolean;
    expiresIn: number;
    resendAvailableIn: number;
    approvalMode: 'automatic' | 'manual';
    approvalMessage: string;
}

function formatRemaining(seconds: number): string {
    const safeSeconds = Math.max(0, seconds);
    const minutes = Math.floor(safeSeconds / 60);
    const remainingSeconds = safeSeconds % 60;

    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds
        .toString()
        .padStart(2, '0')}`;
}

export default function VerifyWhatsApp() {
    const { verification, auth } = usePage<{
        verification: VerificationProps;
        auth: { user: { whatsapp: string | null } | null };
    }>().props;
    const [currentTimestamp, setCurrentTimestamp] = useState(() => Date.now());
    const hasWhatsapp = Boolean(auth.user?.whatsapp);
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
    const expiresIn = Math.max(0, countdownBase.expiresIn - elapsedSeconds);
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

    return (
        <>
            <Head title="Verifikasi WhatsApp" />

            <div className="rounded-2xl border border-border/70 bg-card/70 p-5">
                <div className="flex flex-col gap-6">
                    <Form
                        action={WhatsAppVerificationController.send.url()}
                        method="post"
                        options={{ preserveScroll: true }}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="whatsapp">
                                        Nomor WhatsApp
                                    </Label>
                                    <Input
                                        id="whatsapp"
                                        name="whatsapp"
                                        type="tel"
                                        defaultValue={auth.user?.whatsapp ?? ''}
                                        autoComplete="tel"
                                        inputMode="tel"
                                        placeholder="08123456789"
                                        required={!hasWhatsapp}
                                    />
                                    <InputError
                                        message={errors.whatsapp ?? errors.otp}
                                    />
                                </div>

                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <Button
                                        type="submit"
                                        variant="outline"
                                        disabled={
                                            processing ||
                                            (hasWhatsapp &&
                                                resendAvailableIn > 0)
                                        }
                                        className="w-full sm:w-auto"
                                    >
                                        {processing ? (
                                            <Spinner />
                                        ) : (
                                            <Clock3 className="size-4" />
                                        )}
                                        {hasWhatsapp && resendAvailableIn > 0
                                            ? `Kirim ulang dalam ${formatRemaining(resendAvailableIn)}`
                                            : hasWhatsapp &&
                                                verification.hasActiveChallenge
                                              ? 'Kirim ulang kode'
                                              : 'Kirim kode'}
                                    </Button>

                                    <div className="text-sm font-medium text-muted-foreground tabular-nums">
                                        {expiresIn > 0
                                            ? formatRemaining(expiresIn)
                                            : '00:00'}
                                    </div>
                                </div>
                            </>
                        )}
                    </Form>

                    <Form
                        action={WhatsAppVerificationController.verify.url()}
                        method="post"
                        options={{
                            preserveScroll: true,
                        }}
                        className="flex flex-col gap-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="code">Kode OTP</Label>
                                    <Input
                                        id="code"
                                        name="code"
                                        type="text"
                                        inputMode="numeric"
                                        autoComplete="one-time-code"
                                        autoFocus
                                        maxLength={6}
                                        required
                                        placeholder="Masukkan 6 digit kode"
                                    />
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
                                    Verifikasi
                                </Button>
                            </>
                        )}
                    </Form>
                </div>

                <div className="mt-6 text-center">
                    <Link
                        href={logout().url}
                        method="post"
                        as="button"
                        className="text-sm text-muted-foreground underline underline-offset-4 transition-colors hover:text-primary"
                    >
                        Bukan akun Anda? Keluar
                    </Link>
                </div>
            </div>
        </>
    );
}

VerifyWhatsApp.layout = {
    title: 'Verifikasi WhatsApp',
    description: 'Masukkan kode verifikasi.',
};
