import { Form, Head, usePage } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import { useEffect, useState } from 'react';
import TextLink from '@/components/common/TextLink';
import { Button } from '@/components/ui/button';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send, submit } from '@/routes/verification';

const OTP_LENGTH = 6;

export default function VerifyEmail({ status }: { status?: string }) {
    const { verification_resend_available_at } = usePage<{
        verification_resend_available_at?: number;
    }>().props;

    const [now, setNow] = useState<number>(() => Math.floor(Date.now() / 1000));
    const [otp, setOtp] = useState<string>('');

    useEffect(() => {
        if (!verification_resend_available_at) {
            return;
        }

        const interval = setInterval(() => {
            setNow(Math.floor(Date.now() / 1000));
        }, 1000);

        return () => clearInterval(interval);
    }, [verification_resend_available_at]);

    const countdown = verification_resend_available_at
        ? Math.max(0, verification_resend_available_at - now)
        : 0;

    return (
        <>
            <Head title="Verifikasi email" />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="flex flex-col gap-6">
                <Form
                    action={submit.url()}
                    method="post"
                    className="flex flex-col gap-4 text-center"
                    resetOnError
                    resetOnSuccess
                    onSuccess={() => setOtp('')}
                >
                    {({ processing, errors }) => (
                        <div className="text-left">
                            <FieldGroup>
                                <Field data-invalid={Boolean(errors.otp)}>
                                    <FieldLabel htmlFor="otp">
                                        Kode OTP
                                    </FieldLabel>
                                    <FieldDescription>
                                        Masukkan 6 digit kode yang dikirim ke
                                        email Anda.
                                    </FieldDescription>
                                    <div className="flex justify-center pt-2">
                                        <InputOTP
                                            id="otp"
                                            name="otp"
                                            maxLength={OTP_LENGTH}
                                            value={otp}
                                            onChange={setOtp}
                                            disabled={processing}
                                            pattern={REGEXP_ONLY_DIGITS}
                                            autoFocus
                                        >
                                            <InputOTPGroup>
                                                {Array.from(
                                                    { length: OTP_LENGTH },
                                                    (_, index) => (
                                                        <InputOTPSlot
                                                            key={index}
                                                            index={index}
                                                            aria-invalid={Boolean(
                                                                errors.otp,
                                                            )}
                                                        />
                                                    ),
                                                )}
                                            </InputOTPGroup>
                                        </InputOTP>
                                    </div>
                                    <FieldError>{errors.otp}</FieldError>
                                </Field>
                            </FieldGroup>
                            <Button
                                type="submit"
                                disabled={
                                    processing || otp.length !== OTP_LENGTH
                                }
                                size={'lg'}
                                className="mt-4 w-full"
                            >
                                {processing && <Spinner />}
                                Verifikasi OTP
                            </Button>
                        </div>
                    )}
                </Form>

                <Form
                    action={send.url()}
                    method="post"
                    className="flex flex-col gap-6 border-t pt-6 text-center"
                >
                    {({ processing, errors }) => (
                        <div className="grid gap-4">
                            <p className="text-sm text-gray-500">
                                Belum menerima email OTP?
                            </p>
                            <Button
                                disabled={processing || countdown > 0}
                                variant="secondary"
                                size={'lg'}
                                className="w-full"
                            >
                                {processing && <Spinner />}
                                {countdown > 0
                                    ? `Kirim Ulang OTP (${countdown}s)`
                                    : 'Kirim Ulang OTP'}
                            </Button>

                            {errors.resend && (
                                <span className="text-sm text-red-500">
                                    {errors.resend}
                                </span>
                            )}

                            <TextLink
                                href={logout()}
                                className="mx-auto block text-sm"
                            >
                                Keluar
                            </TextLink>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verifikasi email',
    description:
        'Masukkan kode OTP yang telah dikirimkan ke email Anda untuk memverifikasi akun.',
};
