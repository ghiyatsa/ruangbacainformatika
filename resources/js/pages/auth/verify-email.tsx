import { Form, Head, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import TextLink from '@/components/common/TextLink';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send, submit } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    const { verification_resend_available_at } = usePage<{
        verification_resend_available_at?: number;
    }>().props;

    const [now, setNow] = useState<number>(() => Math.floor(Date.now() / 1000));

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
            <Head title="Email verification" />

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
                >
                    {({ processing, errors }) => (
                        <div className="grid gap-4 text-left">
                            <div className="grid gap-2">
                                <Label htmlFor="otp">Kode OTP</Label>
                                <Input
                                    id="otp"
                                    name="otp"
                                    type="text"
                                    required
                                    placeholder="Masukkan 6 digit OTP"
                                    maxLength={6}
                                    className="text-center text-lg tracking-widest"
                                />
                                {errors.otp && (
                                    <span className="text-sm text-red-500">
                                        {errors.otp}
                                    </span>
                                )}
                            </div>
                            <Button
                                type="submit"
                                disabled={processing}
                                size={'lg'}
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
                                Log out
                            </TextLink>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verify email',
    description:
        'Masukkan kode OTP yang telah dikirimkan ke email Anda untuk memverifikasi akun.',
};
