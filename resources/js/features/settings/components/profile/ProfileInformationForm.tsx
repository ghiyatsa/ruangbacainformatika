import { Form, Link } from '@inertiajs/react';
import { AtSign, CheckCircle2, Phone, User } from 'lucide-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';
import { cn } from '@/lib/utils';
import { send } from '@/routes/verification';
import type { User as AuthUser } from '@/types/auth';

export interface ProfileInformationFormProps {
    user: AuthUser;
    mustVerifyEmail: boolean;
    status?: string;
}

export function ProfileInformationForm({
    user,
    mustVerifyEmail,
    status,
}: ProfileInformationFormProps) {
    return (
        <section className="flex flex-col gap-6">
            <SettingsSectionHeader
                title="Informasi profil"
                description="Perbarui informasi profil dan alamat email akun Anda."
            />

            <Form
                {...ProfileController.update.form()}
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
                            <Label
                                htmlFor="whatsapp"
                                className="flex items-center gap-1.5"
                            >
                                <Phone className="h-3.5 w-3.5 text-muted-foreground" />
                                Nomor WhatsApp
                            </Label>
                            <Input
                                id="whatsapp"
                                type="tel"
                                className="w-full"
                                defaultValue={user.whatsapp ?? ''}
                                name="whatsapp"
                                autoComplete="tel"
                                placeholder="08123456789"
                            />
                            <InputError message={errors.whatsapp} />
                        </div>

                        {mustVerifyEmail && user.email_verified_at === null ? (
                            <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-800/40 dark:bg-amber-950/30">
                                <p className="text-sm text-amber-700 dark:text-amber-400">
                                    Alamat email Anda belum diverifikasi.{' '}
                                    <Link
                                        href={send()}
                                        as="button"
                                        className="font-medium underline underline-offset-4 transition-opacity hover:opacity-75"
                                    >
                                        Kirim ulang email verifikasi.
                                    </Link>
                                </p>
                                {status === 'verification-link-sent' ? (
                                    <p className="mt-1.5 text-sm font-medium text-green-600 dark:text-green-400">
                                        Tautan verifikasi baru telah dikirim ke
                                        email Anda.
                                    </p>
                                ) : null}
                            </div>
                        ) : null}

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
