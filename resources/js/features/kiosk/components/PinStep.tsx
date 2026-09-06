import { useForm } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import { useEffect, useRef } from 'react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import { KioskField } from '@/features/kiosk/components/KioskField';

const PIN_LENGTH = 6;
const AUTO_SUBMIT_DELAY_MS = 350;

export function PinStep() {
    const submitTimeoutRef = useRef<number | null>(null);
    const lastPinRef = useRef<string>('');
    const form = useForm({
        pin: '',
    });

    useEffect(() => {
        if (submitTimeoutRef.current) {
            window.clearTimeout(submitTimeoutRef.current);
            submitTimeoutRef.current = null;
        }

        if (form.data.pin !== lastPinRef.current) {
            lastPinRef.current = form.data.pin;
        } else {
            return;
        }

        if (
            form.processing ||
            form.errors.pin ||
            form.data.pin.length !== PIN_LENGTH
        ) {
            return;
        }

        submitTimeoutRef.current = window.setTimeout(() => {
            form.post(KioskController.verifyPin.url(), {
                preserveScroll: true,
                onFinish: () => {
                    submitTimeoutRef.current = null;
                },
            });
        }, AUTO_SUBMIT_DELAY_MS);

        return () => {
            if (submitTimeoutRef.current) {
                window.clearTimeout(submitTimeoutRef.current);
                submitTimeoutRef.current = null;
            }
        };
    }, [form, form.data.pin, form.processing, form.errors.pin]);

    const submitPin = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (submitTimeoutRef.current) {
            window.clearTimeout(submitTimeoutRef.current);
            submitTimeoutRef.current = null;
        }

        form.post(KioskController.verifyPin.url(), {
            preserveScroll: true,
        });
    };

    return (
        <div className="mx-auto flex w-full max-w-sm justify-center">
            <Card className="w-full border-border/70 shadow-sm">
                <CardHeader className="flex flex-col items-center justify-center space-y-2 text-center">
                    <div className="flex size-11 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <KeyRound className="size-5" />
                    </div>
                    <div className="space-y-1">
                        <h2 className="text-base font-semibold">
                            Buka Akses Kiosk
                        </h2>
                        <p className="text-xs text-muted-foreground">
                            Masukkan 6 digit PIN untuk memulai sesi hari ini.
                        </p>
                    </div>
                </CardHeader>
                <CardContent>
                    <form
                        onSubmit={submitPin}
                        autoComplete="off"
                        className="flex flex-col gap-5"
                    >
                        <KioskField
                            label="PIN Kiosk"
                            htmlFor="kiosk-pin"
                            error={form.errors.pin}
                            required
                        >
                            <InputOTP
                                id="kiosk-pin"
                                autoFocus
                                maxLength={PIN_LENGTH}
                                value={form.data.pin}
                                onChange={(value) => {
                                    form.setData(
                                        'pin',
                                        value.replace(/\D+/g, ''),
                                    );
                                    form.clearErrors('pin');
                                }}
                                inputMode="numeric"
                                pattern="^[0-9]+$"
                                aria-invalid={Boolean(form.errors.pin)}
                                containerClassName="w-full justify-center"
                                disabled={form.processing}
                            >
                                <InputOTPGroup>
                                    {Array.from(
                                        { length: PIN_LENGTH },
                                        (_, index) => (
                                            <InputOTPSlot
                                                key={index}
                                                index={index}
                                                type="password"
                                                className="size-11 text-base"
                                            />
                                        ),
                                    )}
                                </InputOTPGroup>
                            </InputOTP>
                        </KioskField>

                        <Button
                            type="submit"
                            size="lg"
                            className="w-full"
                            disabled={
                                form.processing ||
                                form.data.pin.length !== PIN_LENGTH
                            }
                        >
                            {form.processing ? <Spinner /> : null}
                            {form.processing
                                ? 'Memverifikasi...'
                                : 'Buka Kiosk'}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}
