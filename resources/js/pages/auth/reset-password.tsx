import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import PasswordRequirements from '@/components/common/PasswordRequirements';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { PASSWORD_MIN_LENGTH } from '@/lib/password-requirements';
import { update } from '@/routes/password';
import { useState } from 'react';

type Props = {
    token: string;
    email: string;
};

export default function ResetPassword({ token, email }: Props) {
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const passwordsDoNotMatch =
        passwordConfirmation.length > 0 && password !== passwordConfirmation;

    return (
        <>
            <Head title="Atur ulang password" />

            <div className="flex flex-col gap-6">
                <Form
                    action={update()}
                    transform={(data) => ({ ...data, token, email })}
                    resetOnSuccess={['password', 'password_confirmation']}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <div className="grid gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autoComplete="email"
                                    value={email}
                                    className="mt-1 block w-full"
                                    readOnly
                                />
                                <InputError
                                    message={errors.email}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    autoComplete="new-password"
                                    className="mt-1 block w-full"
                                    autoFocus
                                    placeholder="Masukkan password baru"
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
                                    autoComplete="new-password"
                                    className="mt-1 block w-full"
                                    placeholder="Ulangi password baru"
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
                                    className="mt-2"
                                />
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                                data-test="reset-password-button"
                                size={'lg'}
                            >
                                {processing && <Spinner />}
                                Simpan password baru
                            </Button>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

ResetPassword.layout = {
    title: 'Atur ulang password',
    description: 'Masukkan password baru Anda di bawah ini',
};
