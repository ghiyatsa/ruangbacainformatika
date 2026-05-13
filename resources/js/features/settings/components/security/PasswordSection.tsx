import { Form } from '@inertiajs/react';
import { CheckCircle2, Key } from 'lucide-react';
import type { RefObject } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';

interface PasswordSectionProps {
    currentPasswordInput: RefObject<HTMLInputElement | null>;
    passwordInput: RefObject<HTMLInputElement | null>;
}

export function PasswordSection({
    currentPasswordInput,
    passwordInput,
}: PasswordSectionProps) {
    return (
        <section className="flex flex-col gap-6">
            <SettingsSectionHeader
                title="Perbarui kata sandi"
                description="Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman."
                icon={Key}
            />

            <Form
                action={SecurityController.update()}
                options={{ preserveScroll: true }}
                resetOnError={[
                    'password',
                    'password_confirmation',
                    'current_password',
                ]}
                resetOnSuccess
                onError={(formErrors) => {
                    if (formErrors.password) {
                        passwordInput.current?.focus();
                    }

                    if (formErrors.current_password) {
                        currentPasswordInput.current?.focus();
                    }
                }}
                className="flex flex-col gap-5"
            >
                {({ errors: formErrors, processing, recentlySuccessful }) => (
                    <div className="flex flex-col gap-5">
                        <div className="grid gap-2">
                            <Label htmlFor="current_password">
                                Kata sandi saat ini
                            </Label>
                            <PasswordInput
                                id="current_password"
                                ref={currentPasswordInput}
                                name="current_password"
                                className="w-full"
                                autoComplete="current-password"
                                placeholder="Kata sandi saat ini"
                            />
                            <InputError
                                message={formErrors.current_password}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">Kata sandi baru</Label>
                            <PasswordInput
                                id="password"
                                ref={passwordInput}
                                name="password"
                                className="w-full"
                                autoComplete="new-password"
                                placeholder="Kata sandi baru"
                            />
                            <InputError message={formErrors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">
                                Konfirmasi kata sandi baru
                            </Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                className="w-full"
                                autoComplete="new-password"
                                placeholder="Konfirmasi kata sandi baru"
                            />
                            <InputError
                                message={formErrors.password_confirmation}
                            />
                        </div>

                        <div className="flex items-center gap-3 pt-1">
                            <Button
                                disabled={processing}
                                data-test="update-password-button"
                            >
                                {processing
                                    ? 'Menyimpan...'
                                    : 'Simpan kata sandi'}
                            </Button>
                            {recentlySuccessful ? (
                                <span className="flex items-center gap-1.5 text-sm font-medium text-green-600 dark:text-green-400">
                                    <CheckCircle2 className="h-4 w-4" />
                                    Tersimpan
                                </span>
                            ) : null}
                        </div>
                    </div>
                )}
            </Form>
        </section>
    );
}
