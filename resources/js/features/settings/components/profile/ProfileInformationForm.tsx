import { Form, Link } from '@inertiajs/react';
import { AtSign, CheckCircle2, MapPin, Phone, User } from 'lucide-react';

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
import { cn } from '@/lib/utils';
import settings from '@/routes/settings';
import type { User as AuthUser } from '@/types/auth';

export interface ProfileInformationFormProps {
    user: AuthUser;
}

export function ProfileInformationForm({ user }: ProfileInformationFormProps) {
    const isEditingWhatsapp = false;
    const hasVerifiedWhatsapp = Boolean(user.whatsapp_verified_at);

    return (
        <section className="flex flex-col gap-6">
            <SettingsSectionHeader
                title="Informasi profil"
                description="Perbarui nama, WhatsApp, dan alamat akun Anda."
            />

            <Form
                action={ProfileController.update()}
                options={{
                    preserveScroll: true,
                }}
                className="flex flex-col gap-5"
            >
                {({ processing, errors, recentlySuccessful }) => (
                    <div className="flex flex-col gap-5">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Nama lengkap</Label>
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
                            <Label htmlFor="email">Alamat email</Label>
                            <InputGroup className="bg-muted/50">
                                <InputGroupInput
                                    id="email"
                                    type="email"
                                    className="cursor-not-allowed text-muted-foreground"
                                    value={user.email}
                                    autoComplete="username"
                                    placeholder="Alamat email"
                                    readOnly
                                    disabled
                                    suppressHydrationWarning
                                />
                                <InputGroupAddon>
                                    <AtSign className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <p className="text-xs text-muted-foreground">
                                Email tidak dapat diubah.
                            </p>
                        </div>

                        <div className="grid gap-2">
                            <div className="flex items-center justify-between gap-3">
                                <Label htmlFor="whatsapp">
                                    Nomor WhatsApp
                                </Label>
                                {hasVerifiedWhatsapp && !isEditingWhatsapp ? (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        asChild
                                    >
                                        <Link
                                            href={settings.profile.changeWhatsapp().url}
                                            method="post"
                                            as="button"
                                        >
                                            Ubah nomor
                                        </Link>
                                    </Button>
                                ) : null}
                            </div>
                            <InputGroup
                                className={cn(
                                    hasVerifiedWhatsapp && !isEditingWhatsapp
                                        ? 'bg-muted/50'
                                        : null,
                                )}
                            >
                                <InputGroupInput
                                    id="whatsapp"
                                    type="tel"
                                    className={cn(
                                        hasVerifiedWhatsapp &&
                                            !isEditingWhatsapp
                                            ? 'text-muted-foreground'
                                            : null,
                                    )}
                                    defaultValue={user.whatsapp ?? ''}
                                    name="whatsapp"
                                    readOnly={
                                        hasVerifiedWhatsapp &&
                                        !isEditingWhatsapp
                                    }
                                    autoComplete="tel"
                                    placeholder="08123456789"
                                />
                                <InputGroupAddon>
                                    <Phone className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <p className="text-xs text-muted-foreground">
                                {hasVerifiedWhatsapp && !isEditingWhatsapp
                                    ? 'Nomor WhatsApp sudah terverifikasi.'
                                    : hasVerifiedWhatsapp
                                      ? 'Nomor baru akan meminta verifikasi ulang sebelum layanan anggota aktif kembali.'
                                      : 'Gunakan nomor WhatsApp yang aktif.'}
                            </p>
                            <InputError message={errors.whatsapp} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="address">Alamat</Label>
                            <InputGroup>
                                <InputGroupTextarea
                                    id="address"
                                    className="min-h-28 w-full resize-y"
                                    defaultValue={user.address ?? ''}
                                    name="address"
                                    autoComplete="street-address"
                                    placeholder="Alamat lengkap Anda"
                                />
                                <InputGroupAddon className="self-start pt-2.5">
                                    <MapPin className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <InputError message={errors.address} />
                        </div>

                        <div className="flex items-center gap-3 pt-1">
                            <Button
                                disabled={processing}
                                data-test="update-profile-button"
                            >
                                {processing
                                    ? 'Menyimpan...'
                                    : 'Simpan perubahan'}
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
