import { Form, Head } from '@inertiajs/react';
import { store } from '@/actions/Laravel/Fortify/Http/Controllers/RegisteredUserController';
import GoogleIcon from '@/components/common/GoogleIcon';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import PasswordRequirements from '@/components/common/PasswordRequirements';
import TextLink from '@/components/common/TextLink';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { PASSWORD_MIN_LENGTH } from '@/lib/password-requirements';
import { cn } from '@/lib/utils';
import { login } from '@/routes';
import { google } from '@/routes/auth';
import { useState } from 'react';

type Props = {
    canLoginWithGoogle: boolean;
};

export default function Register({ canLoginWithGoogle }: Props) {
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const passwordsDoNotMatch =
        passwordConfirmation.length > 0 && password !== passwordConfirmation;

    return (
        <>
            <Head title="Daftar" />

            <div className="flex flex-col gap-6">
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
                    disableWhileProcessing
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            {canLoginWithGoogle ? (
                                <div className="relative text-center text-xs tracking-[0.2em] text-muted-foreground uppercase">
                                    <span className="relative z-10 bg-card px-3">
                                        Atau daftar dengan email
                                    </span>
                                    <div className="absolute inset-x-0 top-1/2 border-t border-border" />
                                </div>
                            ) : null}

                            <div className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Nama lengkap</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="name"
                                        placeholder="Nama lengkap"
                                        suppressHydrationWarning
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Alamat email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        tabIndex={2}
                                        autoComplete="email"
                                        placeholder="email@example.com"
                                        suppressHydrationWarning
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">Password</Label>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        required
                                        tabIndex={3}
                                        autoComplete="new-password"
                                        placeholder="Masukkan password"
                                        minLength={PASSWORD_MIN_LENGTH}
                                        value={password}
                                        onChange={(event) =>
                                            setPassword(event.target.value)
                                        }
                                    />
                                    <PasswordRequirements password={password} />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Konfirmasi password
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        required
                                        tabIndex={4}
                                        autoComplete="new-password"
                                        placeholder="Ulangi password"
                                        minLength={PASSWORD_MIN_LENGTH}
                                        value={passwordConfirmation}
                                        onChange={(event) =>
                                            setPasswordConfirmation(
                                                event.target.value,
                                            )
                                        }
                                    />
                                    {passwordsDoNotMatch ? (
                                        <p className="text-sm text-amber-600 dark:text-amber-400">
                                            Konfirmasi password belum sama.
                                        </p>
                                    ) : null}
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    tabIndex={5}
                                    disabled={processing}
                                    size={'lg'}
                                >
                                    {processing ? <Spinner /> : null}
                                    Buat akun
                                </Button>
                            </div>

                            <div className="text-center text-sm text-muted-foreground">
                                Sudah punya akun?{' '}
                                <TextLink href={login()} tabIndex={6}>
                                    Masuk
                                </TextLink>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

Register.layout = {
    title: 'Buat akun baru',
    description: 'Isi data Anda di bawah untuk membuat akun',
};
