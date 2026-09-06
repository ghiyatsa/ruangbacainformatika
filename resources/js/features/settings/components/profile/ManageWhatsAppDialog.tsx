import { router } from '@inertiajs/react';
import { CheckCircle2, Phone } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatCountdown } from '@/lib/format-countdown';
import settings from '@/routes/settings';

export interface VerificationProps {
    maskedWhatsapp: string | null;
    hasActiveChallenge: boolean;
    expiresIn: number;
    resendAvailableIn: number;
    approvalMode: 'automatic' | 'manual';
    approvalMessage: string;
}

interface ManageWhatsAppDialogProps {
    currentWhatsapp: string | null;
    isVerified: boolean;
    verification?: VerificationProps | null;
    triggerText?: string;
}

export function ManageWhatsAppDialog({
    currentWhatsapp,
    isVerified,
    verification,
    triggerText,
}: ManageWhatsAppDialogProps) {
    const [open, setOpen] = useState(false);
    const [phone, setPhone] = useState(currentWhatsapp ?? '');
    const [code, setCode] = useState('');
    const [userTriggeredOtp, setUserTriggeredOtp] = useState(false);
    const otpSent =
        userTriggeredOtp || Boolean(verification?.hasActiveChallenge);
    const [sending, setSending] = useState(false);
    const [verifying, setVerifying] = useState(false);
    const [sendSuccessMsg, setSendSuccessMsg] = useState<string | null>(null);
    const [errorMsg, setErrorMsg] = useState<string | null>(null);

    const [countdown, setCountdown] = useState(
        verification?.resendAvailableIn ?? 0,
    );

    const otpInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (countdown <= 0) {
            return;
        }

        const timer = setInterval(() => {
            setCountdown((prev) => (prev > 1 ? prev - 1 : 0));
        }, 1000);

        return () => clearInterval(timer);
    }, [countdown]);

    const handleSendOtp = () => {
        if (!phone.trim()) {
            setErrorMsg('Nomor WhatsApp wajib diisi.');

            return;
        }

        setErrorMsg(null);
        setSendSuccessMsg(null);
        setSending(true);

        router.post(
            settings.profile.whatsapp.send.url(),
            { whatsapp: phone },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setUserTriggeredOtp(true);
                    setSending(false);
                    setCountdown(60);
                    setSendSuccessMsg(
                        `Kode OTP telah dikirim ke WhatsApp ${phone}.`,
                    );
                    setTimeout(() => {
                        otpInputRef.current?.focus();
                    }, 100);
                },
                onError: (errors) => {
                    setErrorMsg(
                        errors.whatsapp ||
                            errors.otp ||
                            'Gagal mengirim kode verifikasi.',
                    );
                    setSending(false);
                },
            },
        );
    };

    const handleVerifyOtp = (e: React.FormEvent) => {
        e.preventDefault();

        if (code.length !== 6) {
            setErrorMsg('Masukkan 6 digit kode OTP yang diterima.');

            return;
        }

        setErrorMsg(null);
        setVerifying(true);

        router.post(
            settings.profile.whatsapp.verify.url(),
            { code },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setVerifying(false);
                    setOpen(false);
                    setCode('');
                    setUserTriggeredOtp(false);
                    setSendSuccessMsg(null);
                },
                onError: (errors) => {
                    setErrorMsg(
                        errors.code ||
                            errors.otp ||
                            'Kode verifikasi tidak sesuai atau sudah kedaluwarsa.',
                    );
                    setVerifying(false);
                },
            },
        );
    };

    const defaultTrigger = isVerified
        ? 'Ubah Nomor'
        : currentWhatsapp
          ? 'Verifikasi Sekarang'
          : 'Tambah WhatsApp';

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) => {
                setOpen(nextOpen);

                if (nextOpen) {
                    setPhone(currentWhatsapp ?? '');
                    setErrorMsg(null);
                }
            }}
        >
            <DialogTrigger asChild>
                <Button variant={isVerified ? 'outline' : 'default'} size="sm">
                    <Phone className="mr-1.5 size-3.5" />
                    {triggerText ?? defaultTrigger}
                </Button>
            </DialogTrigger>

            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {isVerified
                            ? 'Ubah Nomor WhatsApp'
                            : 'Verifikasi WhatsApp'}
                    </DialogTitle>
                    <DialogDescription>
                        {isVerified
                            ? 'Nomor lama tetap aktif sampai nomor baru berhasil diverifikasi.'
                            : 'Verifikasi nomor WhatsApp untuk keperluan peminjaman dan notifikasi.'}
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleVerifyOtp} className="space-y-4 pt-1">
                    <div className="space-y-1.5">
                        <Label
                            htmlFor="dialog-whatsapp"
                            className="text-sm font-medium"
                        >
                            Nomor WhatsApp
                        </Label>
                        <div className="flex gap-2">
                            <InputGroup className="flex-1">
                                <InputGroupInput
                                    id="dialog-whatsapp"
                                    type="tel"
                                    value={phone}
                                    onChange={(e) => {
                                        setPhone(e.target.value);
                                        setSendSuccessMsg(null);
                                    }}
                                    placeholder="08123456789"
                                    autoComplete="tel"
                                    required
                                />
                                <InputGroupAddon>
                                    <Phone className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>

                            <Button
                                type="button"
                                variant={otpSent ? 'outline' : 'default'}
                                onClick={handleSendOtp}
                                disabled={
                                    sending || !phone.trim() || countdown > 0
                                }
                                className="shrink-0"
                            >
                                {sending ? (
                                    <Spinner className="mr-1.5 size-3.5" />
                                ) : null}
                                {countdown > 0
                                    ? formatCountdown(countdown)
                                    : otpSent
                                      ? 'Kirim Ulang'
                                      : 'Kirim Kode'}
                            </Button>
                        </div>

                        {sendSuccessMsg ? (
                            <p className="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                                <CheckCircle2 className="size-3.5 shrink-0" />
                                {sendSuccessMsg}
                            </p>
                        ) : null}
                    </div>

                    {otpSent ? (
                        <div className="space-y-1.5 pt-1">
                            <Label
                                htmlFor="dialog-otp"
                                className="text-sm font-medium"
                            >
                                Kode Verifikasi
                            </Label>

                            <InputGroup>
                                <InputGroupInput
                                    ref={otpInputRef}
                                    id="dialog-otp"
                                    type="text"
                                    inputMode="numeric"
                                    maxLength={6}
                                    value={code}
                                    onChange={(e) =>
                                        setCode(
                                            e.target.value.replace(/\D/g, ''),
                                        )
                                    }
                                    placeholder="123456"
                                    className="h-11 text-center font-mono text-lg tracking-widest"
                                    required
                                    autoFocus
                                />
                            </InputGroup>
                        </div>
                    ) : null}

                    <InputError message={errorMsg ?? undefined} />

                    <DialogFooter className="gap-2 pt-3 sm:gap-0">
                        <Button
                            type="button"
                            variant="ghost"
                            onClick={() => setOpen(false)}
                            disabled={verifying}
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            disabled={
                                verifying || !otpSent || code.length !== 6
                            }
                        >
                            {verifying ? (
                                <Spinner className="mr-2 size-4" />
                            ) : null}
                            Verifikasi Nomor
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
