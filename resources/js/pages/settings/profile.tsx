import { Form, Head, Link, setLayoutProps, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/common/Heading';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import settings from '@/routes/settings';
import { send } from '@/routes/verification';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage().props;

    setLayoutProps({
        title: 'Pengaturan profil',
        description: 'Kelola nama, nomor WhatsApp, dan informasi akun Anda.',
    });

    return (
        <>
            <Head title="Pengaturan profil" />

            <h1 className="sr-only">Pengaturan profil</h1>

            <div className="flex flex-col gap-12">
                <section className="flex flex-col gap-6">
                    <Heading
                        variant="small"
                        title="Informasi profil"
                        description="Perbarui informasi profil dan alamat email akun Anda."
                    />

                    <Form
                        {...ProfileController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        className="flex flex-col gap-6"
                    >
                        {({ processing, errors }) => (
                            <div className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Nama</Label>

                                    <Input
                                        id="name"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder="Full name"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={errors.name}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Alamat email</Label>

                                    <Input
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        value={auth.user.email}
                                        autoComplete="username"
                                        placeholder="Email address"
                                        readOnly
                                        disabled
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="whatsapp">
                                        Nomor WhatsApp
                                    </Label>
                                    <Input
                                        id="whatsapp"
                                        type="tel"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.whatsapp ?? ''}
                                        name="whatsapp"
                                        autoComplete="tel"
                                        placeholder="08123456789"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.whatsapp}
                                    />
                                </div>

                                {mustVerifyEmail &&
                                    auth.user.email_verified_at === null && (
                                        <div>
                                            <p className="-mt-4 text-sm text-muted-foreground">
                                                Your email address is
                                                unverified.{' '}
                                                <Link
                                                    href={send()}
                                                    as="button"
                                                    className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                                >
                                                    Click here to resend the
                                                    verification email.
                                                </Link>
                                            </p>

                                            {status ===
                                                'verification-link-sent' && (
                                                <div className="mt-2 text-sm font-medium text-green-600">
                                                    A new verification link has
                                                    been sent to your email
                                                    address.
                                                </div>
                                            )}
                                        </div>
                                    )}

                                <div className="flex items-center gap-4">
                                    <Button
                                        disabled={processing}
                                        data-test="update-profile-button"
                                    >
                                        Simpan perubahan
                                    </Button>
                                </div>
                            </div>
                        )}
                    </Form>
                </section>
            </div>
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Pengaturan profil',
            href: settings.profile.edit(),
        },
    ],
};
