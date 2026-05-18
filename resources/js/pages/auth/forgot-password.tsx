// Components
import { Form, Head, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useEffect, useState } from 'react';
import InputError from '@/components/common/InputError';
import TextLink from '@/components/common/TextLink';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { login } from '@/routes';
import { email } from '@/routes/password';

export default function ForgotPassword({ status }: { status?: string }) {
    const { password_reset_resend_available_at } = usePage<{
        password_reset_resend_available_at?: number;
    }>().props;

    const [now, setNow] = useState<number>(() => Math.floor(Date.now() / 1000));

    useEffect(() => {
        if (!password_reset_resend_available_at) {
            return;
        }

        const interval = setInterval(() => {
            setNow(Math.floor(Date.now() / 1000));
        }, 1000);

        return () => clearInterval(interval);
    }, [password_reset_resend_available_at]);

    const countdown = password_reset_resend_available_at
        ? Math.max(0, password_reset_resend_available_at - now)
        : 0;

    return (
        <>
            <Head title="Lupa password" />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="flex flex-col gap-6">
                <Form
                    action={email.url()}
                    method="post"
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <div className="grid gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="email">Alamat email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autoComplete="off"
                                    autoFocus
                                    placeholder="email@example.com"
                                />

                                <InputError message={errors.email} />
                            </div>

                            <Button
                                className="w-full"
                                disabled={processing || countdown > 0}
                                data-test="email-password-reset-link-button"
                                size={'lg'}
                            >
                                {processing && (
                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                )}
                                {countdown > 0
                                    ? `Kirim ulang tautan (${countdown}s)`
                                    : 'Kirim tautan reset password'}
                            </Button>
                        </div>
                    )}
                </Form>

                <div className="space-x-1 text-center text-sm text-muted-foreground">
                    <span>Atau kembali ke</span>
                    <TextLink href={login()}>halaman masuk</TextLink>
                </div>
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Lupa password',
    description: 'Masukkan email Anda untuk menerima tautan reset password',
};
