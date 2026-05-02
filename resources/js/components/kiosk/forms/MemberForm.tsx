import { Form } from '@inertiajs/react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import PasswordInput from '@/components/common/PasswordInput';
import { KioskField } from '@/components/kiosk/KioskField';
import { Button } from '@/components/ui/button';
import { FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { FormBackButton } from './FormBackButton';

export function MemberForm({ onBack }: { onBack: () => void }) {
    return (
        <Form
            action={KioskController.storeMember.url()}
            method="post"
            resetOnError
            resetOnSuccess={['password', 'password_confirmation']}
            disableWhileProcessing
            className="flex flex-col gap-6"
        >
            {({ errors, processing }) => (
                <>
                    <FormBackButton onBack={onBack} />

                    <FieldGroup className="grid gap-5 sm:grid-cols-2">
                        <KioskField
                            label="Nama Lengkap"
                            htmlFor="reg-name"
                            error={errors.name}
                            required
                        >
                            <Input
                                id="reg-name"
                                name="name"
                                autoFocus
                                autoComplete="name"
                                placeholder="Nama lengkap"
                                aria-invalid={Boolean(errors.name)}
                            />
                        </KioskField>

                        <KioskField
                            label="Email"
                            htmlFor="reg-email"
                            error={errors.email}
                            required
                        >
                            <Input
                                id="reg-email"
                                name="email"
                                type="email"
                                autoComplete="email"
                                placeholder="nama@unimal.ac.id"
                                aria-invalid={Boolean(errors.email)}
                            />
                        </KioskField>

                        <KioskField
                            label="No. WhatsApp"
                            htmlFor="reg-whatsapp"
                            error={errors.whatsapp}
                            required
                        >
                            <Input
                                id="reg-whatsapp"
                                name="whatsapp"
                                type="tel"
                                autoComplete="tel"
                                placeholder="08123456789"
                                aria-invalid={Boolean(errors.whatsapp)}
                            />
                        </KioskField>

                        <KioskField
                            label="Password"
                            htmlFor="reg-password"
                            error={errors.password}
                            required
                        >
                            <PasswordInput
                                id="reg-password"
                                name="password"
                                required
                                autoComplete="new-password"
                                placeholder="Password"
                                aria-invalid={Boolean(errors.password)}
                            />
                        </KioskField>

                        <KioskField
                            label="Konfirmasi Password"
                            htmlFor="reg-password-confirm"
                            error={errors.password_confirmation}
                            required
                        >
                            <PasswordInput
                                id="reg-password-confirm"
                                name="password_confirmation"
                                required
                                autoComplete="new-password"
                                placeholder="Konfirmasi password"
                                aria-invalid={Boolean(
                                    errors.password_confirmation,
                                )}
                            />
                        </KioskField>
                    </FieldGroup>

                    <Button type="submit" size="lg" disabled={processing}>
                        {processing ? <Spinner /> : null}
                        Daftar Member
                    </Button>
                </>
            )}
        </Form>
    );
}
