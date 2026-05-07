import { Form, Head, Link, usePage } from '@inertiajs/react';
import { AtSign, CheckCircle2, Phone, User } from 'lucide-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { send } from '@/routes/verification';

export interface ProfilePageProps {
    mustVerifyEmail: boolean;
    status?: string;
}

export default function ProfilePage({
    mustVerifyEmail,
    status,
}: ProfilePageProps) {
    const { auth } = usePage().props;

    // Build avatar initials from user name
    const initials = (auth.user.name ?? '')
        .split(' ')
        .slice(0, 2)
        .map((w: string) => w[0]?.toUpperCase() ?? '')
        .join('');

    return (
        <>
            <Head title="Pengaturan profil" />
            <h1 className="sr-only">Pengaturan profil</h1>

            <div className="flex flex-col gap-10">
                {/* Avatar + name banner */}
                <div className="flex items-center gap-4">
                    <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-primary text-primary-foreground text-xl font-semibold shadow-md shadow-primary/20 select-none">
                        {initials || <User className="h-7 w-7" />}
                    </div>
                    <div>
                        <p className="text-base font-semibold leading-tight">
                            {auth.user.name}
                        </p>
                        <p className="text-sm text-muted-foreground">
                            {auth.user.email}
                        </p>
                    </div>
                </div>

                <Separator />

                {/* Form section */}
                <section className="flex flex-col gap-6">
                    <div>
                        <h2 className="text-base font-semibold">
                            Informasi profil
                        </h2>
                        <p className="mt-0.5 text-sm text-muted-foreground">
                            Perbarui informasi profil dan alamat email akun
                            Anda.
                        </p>
                    </div>

                    <Form
                        {...ProfileController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        className="flex flex-col gap-5"
                    >
                        {({ processing, errors, recentlySuccessful }) => (
                            <div className="flex flex-col gap-5">
                                {/* Name field */}
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
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder="Nama lengkap Anda"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                {/* Email field (read-only) */}
                                <div className="grid gap-2">
                                    <Label
                                        htmlFor="email"
                                        className="flex items-center gap-1.5"
                                    >
                                        <AtSign className="h-3.5 w-3.5 text-muted-foreground" />
                                        Alamat email
                                    </Label>
                                    <div className="relative">
                                        <Input
                                            id="email"
                                            type="email"
                                            className={cn(
                                                'w-full',
                                                'cursor-not-allowed bg-muted/50 text-muted-foreground',
                                            )}
                                            value={auth.user.email}
                                            autoComplete="username"
                                            placeholder="Alamat email"
                                            readOnly
                                            disabled
                                            suppressHydrationWarning
                                        />
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Email tidak dapat diubah.
                                    </p>
                                </div>

                                {/* WhatsApp field */}
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
                                        defaultValue={
                                            auth.user.whatsapp ?? ''
                                        }
                                        name="whatsapp"
                                        autoComplete="tel"
                                        placeholder="08123456789"
                                    />
                                    <InputError message={errors.whatsapp} />
                                </div>

                                {/* Email verification notice */}
                                {mustVerifyEmail &&
                                    auth.user.email_verified_at === null && (
                                        <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-800/40 dark:bg-amber-950/30">
                                            <p className="text-sm text-amber-700 dark:text-amber-400">
                                                Alamat email Anda belum
                                                diverifikasi.{' '}
                                                <Link
                                                    href={send()}
                                                    as="button"
                                                    className="font-medium underline underline-offset-4 transition-opacity hover:opacity-75"
                                                >
                                                    Kirim ulang email
                                                    verifikasi.
                                                </Link>
                                            </p>
                                            {status ===
                                                'verification-link-sent' && (
                                                <p className="mt-1.5 text-sm font-medium text-green-600 dark:text-green-400">
                                                    Tautan verifikasi baru
                                                    telah dikirim ke email
                                                    Anda.
                                                </p>
                                            )}
                                        </div>
                                    )}

                                {/* Actions */}
                                <div className="flex items-center gap-3 pt-1">
                                    <Button
                                        disabled={processing}
                                        data-test="update-profile-button"
                                    >
                                        {processing
                                            ? 'Menyimpan…'
                                            : 'Simpan perubahan'}
                                    </Button>

                                    {recentlySuccessful && (
                                        <span className="flex items-center gap-1.5 text-sm font-medium text-green-600 dark:text-green-400">
                                            <CheckCircle2 className="h-4 w-4" />
                                            Tersimpan
                                        </span>
                                    )}
                                </div>
                            </div>
                        )}
                    </Form>
                </section>
            </div>
        </>
    );
}
