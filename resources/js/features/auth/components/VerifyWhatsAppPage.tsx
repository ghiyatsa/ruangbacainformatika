import { Form, Link, usePage } from '@inertiajs/react';
import { CheckCircle2, Clock3, KeyRound, Phone } from 'lucide-react';
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
    const { verification, auth } = usePage<{
        verification: VerificationProps;
        auth: {
            user: { whatsapp: string | null } | null;
            hasVerifiedWhatsApp: boolean;
        };
    }>().props;
    const [currentTimestamp, setCurrentTimestamp] = useState(() => Date.now());
    const [whatsappVal, setWhatsappVal] = useState(auth.user?.whatsapp ?? '');
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
                                <div className="flex gap-2">
                                    <InputGroup className="flex-1">
                                        <InputGroupInput
                                            id="whatsapp"
                                            name="whatsapp"
                                            type="tel"
                                            value={whatsappVal}
                                            onChange={(e) =>
                                                setWhatsappVal(
                                                    e.target.value,
                                                )
                                            }
                                            autoComplete="tel"
                                            inputMode="tel"
                                            placeholder="08123456789"
                                            required={!hasWhatsapp}
                                        />
                                        <InputGroupAddon>
                                            <Phone className="size-4" />
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <Button
                                        type="submit"
                                        variant="outline"
                                        disabled={
                                            processing ||
                                            (hasWhatsapp &&
                                                resendAvailableIn > 0) ||
                                            (auth.hasVerifiedWhatsApp &&
                                                whatsappVal ===
                                                    auth.user?.whatsapp)
                                        }
                                        className="min-w-[110px] shrink-0 tabular-nums"
                                    >
                                        {processing ? (
                                            <Spinner />
                                        ) : resendAvailableIn > 0 ? (
                                            <Clock3 className="size-4" />
                                        ) : null}
                                        {resendAvailableIn > 0
                                            ? formatCountdown(
                                                  resendAvailableIn,
                                              )
                                            : hasWhatsapp &&
                                                verification.hasActiveChallenge
                                              ? 'Kirim Ulang'
                                              : 'Kirim Kode'}
                                    </Button>
                                </div>
                                <InputError
                                    message={errors.whatsapp ?? errors.otp}
                                />
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
                                        placeholder="Masukkan 6 digit kode"
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
    );
}
