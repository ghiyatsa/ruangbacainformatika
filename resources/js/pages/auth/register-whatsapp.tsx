import { Form, Head, Link, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/common/InputError';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';

export default function RegisterWhatsapp() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Lengkapi WhatsApp" />

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
                            <Label htmlFor="email">Email kampus</Label>
                            <Input
                                id="email"
                                type="email"
                                value={auth.user.email}
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
                                autoFocus
                                required
                                autoComplete="tel"
                                placeholder="08123456789"
                            />
                            <p className="text-sm text-muted-foreground">
                                Nomor ini wajib diisi sebelum akun bisa
                                digunakan untuk meminjam buku.
                            </p>
                            <InputError message={errors.whatsapp} />
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
                        className="text-sm text-muted-foreground underline underline-offset-4 hover:text-primary transition-colors"
                    >
                        Bukan akun Anda? Keluar
                    </Link>
                </div>
            </div>
        </>
    );
}

RegisterWhatsapp.layout = {
    title: 'Lengkapi nomor WhatsApp',
    description:
        'Satu langkah terakhir untuk melengkapi profil Anda.',
};
