import { Form, Head } from '@inertiajs/react';
import { store } from '@/actions/Laravel/Fortify/Http/Controllers/AuthenticatedSessionController';
import GoogleIcon from '@/components/common/GoogleIcon';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import TextLink from '@/components/common/TextLink';
import { Button, buttonVariants } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { register } from '@/routes';
import { google } from '@/routes/auth';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    canLoginWithGoogle: boolean;
};

export default function Login({
    status,
    canResetPassword,
    canRegister,
    canLoginWithGoogle,
}: Props) {
    return (
        <>
            <Head title="Masuk" />

            <div className="flex flex-col gap-6">
                {status ? (
                    <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-sm font-medium text-emerald-700">
                        {status}
                    </div>
                ) : null}

                {canLoginWithGoogle ? (
                    <div className="grid gap-3">
                        <a
                            href={google.url()}
                            className={cn(
                                buttonVariants({ variant: 'outline' }),
                                'w-full',
                            )}
                        >
                            <GoogleIcon data-icon="inline-start" />
                            Lanjutkan dengan Google
                        </a>
                    </div>
                ) : null}

                <Form
                    action={store.url()}
                    method="post"
                    resetOnSuccess={['password']}
                    disableWhileProcessing
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            {canLoginWithGoogle ? (
                                <div className="relative text-center text-xs tracking-[0.2em] text-muted-foreground uppercase">
                                    <span className="relative z-10 bg-card px-3">
                                        Atau lanjutkan dengan email
                                    </span>
                                    <div className="absolute inset-x-0 top-1/2 border-t border-border" />
                                </div>
                            ) : null}

                            <div className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="email">Alamat email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="email"
                                        placeholder="email@example.com"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <div className="flex items-center">
                                        <Label htmlFor="password">
                                            Password
                                        </Label>
                                        {canResetPassword ? (
                                            <TextLink
                                                href={request()}
                                                className="ml-auto text-sm"
                                                tabIndex={5}
                                            >
                                                Lupa password?
                                            </TextLink>
                                        ) : null}
                                    </div>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        required
                                        tabIndex={2}
                                        autoComplete="current-password"
                                        placeholder="Masukkan password"
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="flex items-center gap-3">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        value="1"
                                        tabIndex={3}
                                    />
                                    <Label htmlFor="remember">
                                        Ingat saya
                                    </Label>
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="login-button"
                                    size={'lg'}
                                >
                                    {processing ? <Spinner /> : null}
                                    Masuk
                                </Button>
                            </div>

                            {canRegister ? (
                                <div className="text-center text-sm text-muted-foreground">
                                    Belum punya akun?{' '}
                                    <TextLink href={register()} tabIndex={5}>
                                        Daftar
                                    </TextLink>
                                </div>
                            ) : null}
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

Login.layout = {
    title: 'Masuk ke akun Anda',
    description: 'Masukkan email dan password Anda untuk melanjutkan',
};
