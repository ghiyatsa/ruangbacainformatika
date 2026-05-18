import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

export default function ConfirmPassword() {
    return (
        <>
            <Head title="Konfirmasi password" />

            <div className="flex flex-col gap-6">
                <Form
                    action={store()}
                    resetOnSuccess={['password']}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <div className="grid gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    placeholder="Masukkan password"
                                    autoComplete="current-password"
                                    autoFocus
                                />

                                <InputError message={errors.password} />
                            </div>

                            <Button
                                className="w-full"
                                disabled={processing}
                                data-test="confirm-password-button"
                                size={'lg'}
                            >
                                {processing && <Spinner />}
                                Konfirmasi password
                            </Button>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

ConfirmPassword.layout = {
    title: 'Konfirmasi password Anda',
    description:
        'Ini adalah area aman aplikasi. Silakan konfirmasi password Anda sebelum melanjutkan.',
};
