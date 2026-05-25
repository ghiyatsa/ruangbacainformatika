import { Form, Head, Link, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { logout } from '@/routes';

export default function RegisterProfile() {
    const { auth } = usePage().props;
    const user = auth.user!;
    const hasWhatsapp = Boolean(user.whatsapp);
    const hasVerifiedWhatsapp = Boolean(auth.hasVerifiedWhatsApp);

    return (
        <>
            <Head title="Lengkapi Profil" />

            <div className="flex flex-col gap-6">
                <Form
                    action={ProfileController.storeOnboarding.url()}
                    method="patch"
                    options={{
                        preserveScroll: true,
                    }}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <div className="grid gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nama lengkap</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    defaultValue={user.name}
                                    autoFocus
                                    required
                                    autoComplete="name"
                                    placeholder="Nama lengkap"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email kampus</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={user.email}
                                    readOnly
                                    disabled
                                    autoComplete="username"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="whatsapp">Nomor WhatsApp</Label>
                                <Input
                                    id="whatsapp"
                                    name="whatsapp"
                                    type="tel"
                                    defaultValue={user.whatsapp ?? ''}
                                    required
                                    readOnly={hasVerifiedWhatsapp}
                                    disabled={hasVerifiedWhatsapp}
                                    autoComplete="tel"
                                    placeholder="08123456789"
                                />
                                <InputError message={errors.whatsapp} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="address">Alamat</Label>
                                <Textarea
                                    id="address"
                                    name="address"
                                    defaultValue={user.address ?? ''}
                                    required
                                    autoComplete="street-address"
                                    className="min-h-28 resize-y"
                                    placeholder="Alamat lengkap"
                                />
                                <p className="text-sm text-muted-foreground">
                                    {hasWhatsapp
                                        ? 'Lengkapi alamat untuk melanjutkan.'
                                        : 'Nama, WhatsApp, dan alamat wajib diisi.'}
                                </p>
                                <InputError message={errors.address} />
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                                size={'lg'}
                            >
                                {processing ? <Spinner /> : null}
                                Simpan dan lanjutkan
                            </Button>
                        </div>
                    )}
                </Form>

                <div className="text-center">
                    <Link
                        href={logout().url}
                        method="post"
                        as="button"
                        className="text-sm text-muted-foreground underline underline-offset-4 transition-colors hover:text-primary"
                    >
                        Bukan akun Anda? Keluar
                    </Link>
                </div>
            </div>
        </>
    );
}

RegisterProfile.layout = {
    title: 'Lengkapi profil',
    description: 'Lengkapi profil untuk melanjutkan.',
};
