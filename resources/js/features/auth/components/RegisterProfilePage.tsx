import { Form, Link, router, usePage } from '@inertiajs/react';
import {
    AtSign,
    CheckCircle2,
    Clock3,
    KeyRound,
    MapPin,
    Phone,
    User,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import WhatsAppVerificationController from '@/actions/App/Http/Controllers/Auth/WhatsAppVerificationController';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
    InputGroupTextarea,
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
}

export function RegisterProfilePage() {
    const { auth, verification } = usePage<{
        auth: {
            user: {
                name: string;
                email: string;
                whatsapp: string | null;
                address: string | null;
            } | null;
            hasVerifiedWhatsApp: boolean;
        };
        verification?: VerificationProps;
    }>().props;

    const user = auth.user!;
    const hasVerifiedWhatsapp = Boolean(auth.hasVerifiedWhatsApp);

    const [whatsappVal, setWhatsappVal] = useState(user.whatsapp ?? '');
    const [currentTimestamp, setCurrentTimestamp] = useState(() => Date.now());

    const status = verification ?? {
        maskedWhatsapp: null,
        hasActiveChallenge: false,
        expiresIn: 0,
        resendAvailableIn: 0,
    };

    const [countdownBase, setCountdownBase] = useState(() => ({
        resendAvailableIn: status.resendAvailableIn,
        startedAt: currentTimestamp,
    }));

    useEffect(() => {
        const resetCountdownTimeout = window.setTimeout(() => {
            setCountdownBase({
                resendAvailableIn: status.resendAvailableIn,
                startedAt: Date.now(),
            });
        }, 0);

        return () => window.clearTimeout(resetCountdownTimeout);
    }, [status.resendAvailableIn]);

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

    const showOtpInput = !hasVerifiedWhatsapp && status.hasActiveChallenge;

    return (
        <div className="flex flex-col gap-6">
            <Form
                action={ProfileController.storeOnboarding.url()}
                method="patch"
                options={{
                    preserveScroll: true,
                }}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <div className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Nama lengkap</Label>
                            <InputGroup>
                                <InputGroupInput
                                    id="name"
                                    name="name"
                                    type="text"
                                    defaultValue={user.name}
                                    autoFocus
                                    required
                                    autoComplete="name"
                                    placeholder="Nama lengkap Anda"
                                />
                                <InputGroupAddon>
                                    <User className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email kampus</Label>
                            <InputGroup className="bg-muted/50">
                                <InputGroupInput
                                    id="email"
                                    type="email"
                                    className="cursor-not-allowed text-muted-foreground"
                                    value={user.email}
                                    readOnly
                                    disabled
                                    autoComplete="username"
                                />
                                <InputGroupAddon>
                                    <AtSign className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="whatsapp">Nomor WhatsApp</Label>
                            <div className="flex gap-2">
                                <InputGroup
                                    className={
                                        hasVerifiedWhatsapp
                                            ? 'flex-1 bg-muted/50'
                                            : 'flex-1'
                                    }
                                >
                                    <InputGroupInput
                                        id="whatsapp"
                                        name="whatsapp"
                                        type="tel"
                                        className={
                                            hasVerifiedWhatsapp
                                                ? 'text-muted-foreground'
                                                : undefined
                                        }
                                        value={whatsappVal}
                                        onChange={(e) =>
                                            setWhatsappVal(e.target.value)
                                        }
                                        required
                                        readOnly={hasVerifiedWhatsapp}
                                        disabled={hasVerifiedWhatsapp}
                                        autoComplete="tel"
                                        placeholder="08xxxxxxxxxx"
                                    />
                                    <InputGroupAddon>
                                        {hasVerifiedWhatsapp ? (
                                            <CheckCircle2 className="size-4 text-green-600" />
                                        ) : (
                                            <Phone className="size-4" />
                                        )}
                                    </InputGroupAddon>
                                </InputGroup>

                                {!hasVerifiedWhatsapp ? (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        disabled={resendAvailableIn > 0}
                                        onClick={() => {
                                            router.post(
                                                WhatsAppVerificationController.send.url(),
                                                { whatsapp: whatsappVal },
                                                { preserveScroll: true },
                                            );
                                        }}
                                        className="min-w-[110px] shrink-0 tabular-nums"
                                    >
                                        {resendAvailableIn > 0 ? (
                                            <>
                                                <Clock3 className="size-4" />
                                                {formatCountdown(
                                                    resendAvailableIn,
                                                )}
                                            </>
                                        ) : status.hasActiveChallenge ? (
                                            'Kirim Ulang'
                                        ) : (
                                            'Kirim Kode'
                                        )}
                                    </Button>
                                ) : null}
                            </div>
                            <InputError
                                message={errors.whatsapp ?? errors.otp}
                            />
                        </div>

                        {showOtpInput ? (
                            <div className="grid gap-2">
                                <Label htmlFor="code">Kode OTP</Label>
                                <InputGroup>
                                    <InputGroupInput
                                        id="code"
                                        name="code"
                                        type="text"
                                        inputMode="numeric"
                                        autoComplete="one-time-code"
                                        maxLength={6}
                                        required
                                        className="font-mono tracking-[0.2em]"
                                        placeholder="Masukkan 6 digit kode"
                                    />
                                    <InputGroupAddon>
                                        <KeyRound className="size-4" />
                                    </InputGroupAddon>
                                </InputGroup>
                                <InputError message={errors.code} />
                            </div>
                        ) : null}

                        <div className="grid gap-2">
                            <Label htmlFor="address">Alamat</Label>
                            <InputGroup>
                                <InputGroupTextarea
                                    id="address"
                                    name="address"
                                    defaultValue={user.address ?? ''}
                                    required
                                    autoComplete="street-address"
                                    className="min-h-28 resize-y"
                                    placeholder="Alamat tempat tinggal"
                                />
                                <InputGroupAddon className="self-start pt-2.5">
                                    <MapPin className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <InputError message={errors.address} />
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                            size={'lg'}
                        >
                            {processing ? <Spinner /> : null}
                            Simpan dan lanjutkan
                        </Button>
                    </div>
                )}
            </Form>

            <div className="flex flex-col items-center gap-3 text-center">
                <Link
                    href="/register/profile/skip"
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
            </div>
        </div>
    );
}
