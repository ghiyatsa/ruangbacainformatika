import { Form } from '@inertiajs/react';
import { CheckCircle2, MapPin, User } from 'lucide-react';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
    InputGroupTextarea,
} from '@/components/ui/input-group';
import { Label } from '@/components/ui/label';
import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';
import type { User as AuthUser } from '@/types/auth';

export interface ProfileInformationFormProps {
    user: AuthUser;
}

export function ProfileInformationForm({ user }: ProfileInformationFormProps) {
    return (
        <section className="space-y-6">
            <SettingsSectionHeader title="Data Pribadi" />

            <Form
                action={ProfileController.update()}
                options={{
                    preserveScroll: true,
                }}
                className="mt-6 flex flex-col gap-5"
            >
                {({ processing, errors, recentlySuccessful }) => (
                    <div className="flex flex-col gap-5">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Nama Lengkap</Label>
                            <InputGroup>
                                <InputGroupInput
                                    id="name"
                                    defaultValue={user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="Nama lengkap Anda"
                                />
                                <InputGroupAddon>
                                    <User className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="address">Alamat Domisili</Label>
                            <InputGroup>
                                <InputGroupTextarea
                                    id="address"
                                    className="min-h-24 w-full resize-y"
                                    defaultValue={user.address ?? ''}
                                    name="address"
                                    required
                                    autoComplete="street-address"
                                    placeholder="Alamat lengkap tempat tinggal saat ini"
                                />
                                <InputGroupAddon className="self-start pt-2.5">
                                    <MapPin className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <InputError message={errors.address} />
                        </div>

                        <div className="flex items-center gap-3 pt-1">
                            <Button
                                type="submit"
                                disabled={processing}
                                className="w-fit"
                            >
                                Simpan Perubahan
                            </Button>

                            {recentlySuccessful ? (
                                <p className="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                                    <CheckCircle2 className="size-3.5" />
                                    Tersimpan
                                </p>
                            ) : null}
                        </div>
                    </div>
                )}
            </Form>
        </section>
    );
}
