import { Form } from '@inertiajs/react';
import { useState } from 'react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import PasswordInput from '@/components/common/PasswordInput';
import { Button } from '@/components/ui/button';
import { FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { KioskField } from '@/features/kiosk/components/KioskField';

export function MemberForm() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [whatsapp, setWhatsapp] = useState('');
    const [address, setAddress] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');

    const isComplete =
        name.trim() !== '' &&
        email.trim() !== '' &&
        whatsapp.trim() !== '' &&
        address.trim() !== '' &&
        password !== '' &&
        passwordConfirmation !== '';

    return (
        <Form
            action={KioskController.storeMember.url()}
            method="post"
            resetOnError
            resetOnSuccess={['password', 'password_confirmation']}
            disableWhileProcessing
            className="flex flex-col gap-4"
        >
            {({ errors, processing }) => (
                <>
                    <FieldGroup className="grid gap-4 sm:grid-cols-2">
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
                                value={name}
                                onChange={(e) => setName(e.target.value)}
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
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
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
                                value={whatsapp}
                                onChange={(e) => setWhatsapp(e.target.value)}
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
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
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
                                value={passwordConfirmation}
                                onChange={(e) =>
                                    setPasswordConfirmation(e.target.value)
                                }
                                aria-invalid={Boolean(
                                    errors.password_confirmation,
                                )}
                            />
                        </KioskField>

                        <KioskField
                            label="Alamat"
                            htmlFor="reg-address"
                            error={errors.address}
                            required
                            className="sm:col-span-2"
                        >
                            <Textarea
                                id="reg-address"
                                name="address"
                                autoComplete="street-address"
                                placeholder="Alamat lengkap"
                                value={address}
                                onChange={(e) => setAddress(e.target.value)}
                                aria-invalid={Boolean(errors.address)}
                                rows={2}
                            />
                        </KioskField>
                    </FieldGroup>

                    <Button
                        type="submit"
                        size="lg"
                        disabled={processing || !isComplete}
                    >
                        {processing ? <Spinner /> : null}
                        Daftar Member
                    </Button>
                </>
            )}
        </Form>
    );
}
