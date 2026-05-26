import { Form } from '@inertiajs/react';
import { AtSign, CheckCircle2, MapPin, Phone, User } from 'lucide-react';
import { useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';
import { cn } from '@/lib/utils';
import type { User as AuthUser } from '@/types/auth';

export interface ProfileInformationFormProps {
    user: AuthUser;
}

export function ProfileInformationForm({ user }: ProfileInformationFormProps) {
    const [isEditingWhatsapp, setIsEditingWhatsapp] = useState(false);
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
                            <Label
                                htmlFor="name"
                                className="flex items-center gap-1.5"
                            >
                                <User className="h-3.5 w-3.5 text-muted-foreground" />
                                Nama lengkap
                            </Label>
                            <Input
                                id="name"
                                className="w-full"
                                defaultValue={user.name}
                                name="name"
                                required
                                autoComplete="name"
                                placeholder="Nama lengkap Anda"
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label
                                htmlFor="email"
                                className="flex items-center gap-1.5"
                            >
                                <AtSign className="h-3.5 w-3.5 text-muted-foreground" />
                                Alamat email
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                className={cn(
                                    'w-full',
                                    'cursor-not-allowed bg-muted/50 text-muted-foreground',
                                )}
                                value={user.email}
                                autoComplete="username"
                                placeholder="Alamat email"
                                readOnly
                                disabled
                                suppressHydrationWarning
                            />
                            <p className="text-xs text-muted-foreground">
                                Email tidak dapat diubah.
                            </p>
                        </div>

                        <div className="grid gap-2">
                            <div className="flex items-center justify-between gap-3">
                                <Label
                                    htmlFor="whatsapp"
                                    className="flex items-center gap-1.5"
                                >
                                    <Phone className="h-3.5 w-3.5 text-muted-foreground" />
                                    Nomor WhatsApp
                                </Label>
                                {hasVerifiedWhatsapp && !isEditingWhatsapp ? (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() =>
                                            setIsEditingWhatsapp(true)
                                        }
                                    >
                                        Ubah nomor
                                    </Button>
                                ) : null}
                            </div>
                            <Input
                                id="whatsapp"
                                type="tel"
                                className={cn(
                                    'w-full',
                                    hasVerifiedWhatsapp && !isEditingWhatsapp
                                        ? 'bg-muted/50 text-muted-foreground'
                                        : null,
                                )}
                                defaultValue={user.whatsapp ?? ''}
                                name="whatsapp"
                                readOnly={
                                    hasVerifiedWhatsapp && !isEditingWhatsapp
                                }
                                autoComplete="tel"
                                placeholder="08123456789"
                            />
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
                            <Label
                                htmlFor="address"
                                className="flex items-center gap-1.5"
                            >
                                <MapPin className="h-3.5 w-3.5 text-muted-foreground" />
                                Alamat
                            </Label>
                            <Textarea
                                id="address"
                                className="min-h-28 w-full resize-y"
                                defaultValue={user.address ?? ''}
                                name="address"
                                autoComplete="street-address"
                                placeholder="Alamat lengkap Anda"
                            />
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
