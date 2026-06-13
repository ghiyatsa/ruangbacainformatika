import { Form, Link, usePage } from '@inertiajs/react';
import { AtSign, MapPin, Phone, User } from 'lucide-react';
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
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';

export function RegisterProfilePage() {
    const { auth } = usePage().props;
    const user = auth.user!;
    const hasWhatsapp = Boolean(user.whatsapp);
    const hasVerifiedWhatsapp = Boolean(auth.hasVerifiedWhatsApp);

    return (
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
                            <InputGroup>
                                <InputGroupInput
                                    id="name"
                                    name="name"
                                    type="text"
                                    defaultValue={user.name}
                                    autoFocus
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
                            <Label htmlFor="email">Email kampus</Label>
                            <InputGroup className="bg-muted/50">
                                <InputGroupInput
                                    id="email"
                                    type="email"
                                    className="cursor-not-allowed text-muted-foreground"
                                    value={user.email}
                                    readOnly
                                    disabled
                                    autoComplete="username"
                                />
                                <InputGroupAddon>
                                    <AtSign className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="whatsapp">Nomor WhatsApp</Label>
                            <InputGroup
                                className={
                                    hasVerifiedWhatsapp
                                        ? 'bg-muted/50'
                                        : undefined
                                }
                            >
                                <InputGroupInput
                                    id="whatsapp"
                                    name="whatsapp"
                                    type="tel"
                                    className={
                                        hasVerifiedWhatsapp
                                            ? 'text-muted-foreground'
                                            : undefined
                                    }
                                    defaultValue={user.whatsapp ?? ''}
                                    required
                                    readOnly={hasVerifiedWhatsapp}
                                    disabled={hasVerifiedWhatsapp}
                                    autoComplete="tel"
                                    placeholder="08xxxxxxxxxx"
                                />
                                <InputGroupAddon>
                                    <Phone className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <InputError message={errors.whatsapp} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="address">Alamat</Label>
                            <InputGroup>
                                <InputGroupTextarea
                                    id="address"
                                    name="address"
                                    defaultValue={user.address ?? ''}
                                    required
                                    autoComplete="street-address"
                                    className="min-h-28 resize-y"
                                    placeholder="Alamat tempat tinggal"
                                />
                                <InputGroupAddon className="self-start pt-2.5">
                                    <MapPin className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                            <p className="text-sm text-muted-foreground">
                                {hasWhatsapp
                                    ? 'Lengkapi alamat untuk melanjutkan.'
                                    : 'Lengkapi nama, nomor WhatsApp, dan alamat untuk melanjutkan.'}
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
    );
}
