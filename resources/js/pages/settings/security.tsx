import { Form, Head } from '@inertiajs/react';
import { Monitor, ShieldCheck, Smartphone } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import TwoFactorRecoveryCodes from '@/components/auth/TwoFactorRecoveryCodes';
import TwoFactorSetupModal from '@/components/auth/TwoFactorSetupModal';
import Heading from '@/components/common/Heading';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import settings from '@/routes/settings';
import { disable, enable } from '@/routes/two-factor';

type Session = {
    id: string;
    ip_address: string;
    is_current_device: boolean;
    agent: {
        is_desktop: boolean;
        platform: string;
        browser: string;
    };
    last_active: string;
};

type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
    twoFactorConfirmed?: boolean;
    sessions?: Session[];
};

export default function Security({
    canManageTwoFactor = false,
    requiresConfirmation = false,
    twoFactorEnabled = false,
    twoFactorConfirmed = false,
    sessions = [],
}: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const {
        qrCodeSvg,
        manualSetupKey,
        clearSetupData,
        clearTwoFactorAuthData,
        fetchSetupData,
        recoveryCodesList,
        fetchRecoveryCodes,
        errors,
    } = useTwoFactorAuth();
    const [showSetupModal, setShowSetupModal] = useState<boolean>(false);
    const [confirmingLogout, setConfirmingLogout] = useState(false);
    const prevTwoFactorEnabled = useRef(twoFactorEnabled);

    useEffect(() => {
        if (prevTwoFactorEnabled.current && !twoFactorEnabled) {
            clearTwoFactorAuthData();
        }

        prevTwoFactorEnabled.current = twoFactorEnabled;
    }, [twoFactorEnabled, clearTwoFactorAuthData]);

    const isConfirmed = !requiresConfirmation || twoFactorConfirmed;

    return (
        <>
            <Head title="Pengaturan keamanan" />

            <h1 className="sr-only">Pengaturan keamanan</h1>

            <div className="flex flex-col gap-12">
                <section className="flex flex-col gap-6">
                    <Heading
                        variant="small"
                        title="Perbarui kata sandi"
                        description="Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman."
                    />

                    <Form
                        {...SecurityController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        resetOnError={[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]}
                        resetOnSuccess
                        onError={(errors) => {
                            if (errors.password) {
                                passwordInput.current?.focus();
                            }

                            if (errors.current_password) {
                                currentPasswordInput.current?.focus();
                            }
                        }}
                        className="flex flex-col gap-6"
                    >
                        {({ errors, processing }) => (
                            <div className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password">
                                        Kata sandi saat ini
                                    </Label>

                                    <PasswordInput
                                        id="current_password"
                                        ref={currentPasswordInput}
                                        name="current_password"
                                        className="mt-1 block w-full"
                                        autoComplete="current-password"
                                        placeholder="Kata sandi saat ini"
                                    />

                                    <InputError
                                        message={errors.current_password}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">
                                        Kata sandi baru
                                    </Label>

                                    <PasswordInput
                                        id="password"
                                        ref={passwordInput}
                                        name="password"
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        placeholder="New password"
                                    />

                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Konfirmasi kata sandi baru
                                    </Label>

                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        placeholder="Confirm password"
                                    />

                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button
                                        disabled={processing}
                                        data-test="update-password-button"
                                    >
                                        Simpan kata sandi
                                    </Button>
                                </div>
                            </div>
                        )}
                    </Form>
                </section>

                {canManageTwoFactor && (
                    <section className="flex flex-col gap-6">
                        <Heading
                            variant="small"
                            title="Otentikasi dua faktor"
                            description="Tingkatkan keamanan akun Anda dengan menggunakan otentikasi dua faktor."
                        />
                        {twoFactorEnabled ? (
                            <div className="flex flex-col items-start justify-start gap-4">
                                {isConfirmed ? (
                                    <>
                                        <p className="text-sm text-muted-foreground">
                                            Anda telah mengaktifkan otentikasi
                                            dua faktor. Anda akan dimintai pin
                                            acak yang aman saat login, yang
                                            dapat Anda ambil dari aplikasi yang
                                            didukung TOTP di ponsel Anda.
                                        </p>

                                        <div className="relative inline">
                                            <Form {...disable.form()}>
                                                {({ processing }) => (
                                                    <Button
                                                        variant="destructive"
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        Nonaktifkan 2FA
                                                    </Button>
                                                )}
                                            </Form>
                                        </div>

                                        <TwoFactorRecoveryCodes
                                            recoveryCodesList={
                                                recoveryCodesList
                                            }
                                            fetchRecoveryCodes={
                                                fetchRecoveryCodes
                                            }
                                            errors={errors}
                                        />
                                    </>
                                ) : (
                                    <>
                                        <p className="text-sm text-muted-foreground">
                                            Anda belum selesai mengonfigurasi
                                            otentikasi dua faktor. Selesaikan
                                            pengaturan untuk meningkatkan
                                            keamanan akun Anda.
                                        </p>

                                        <div className="flex items-center gap-3">
                                            <Button
                                                onClick={() =>
                                                    setShowSetupModal(true)
                                                }
                                            >
                                                <ShieldCheck />
                                                Selesaikan pengaturan
                                            </Button>

                                            <Form {...disable.form()}>
                                                {({ processing }) => (
                                                    <Button
                                                        variant="ghost"
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        Batalkan pengaturan
                                                    </Button>
                                                )}
                                            </Form>
                                        </div>
                                    </>
                                )}
                            </div>
                        ) : (
                            <div className="flex flex-col items-start justify-start gap-4">
                                <p className="text-sm text-muted-foreground">
                                    Saat Anda mengaktifkan otentikasi dua
                                    faktor, Anda akan dimintai pin yang aman
                                    saat login. Pin ini dapat diambil dari
                                    aplikasi yang didukung TOTP di ponsel Anda.
                                </p>

                                <div>
                                    <Form
                                        {...enable.form()}
                                        onSuccess={() =>
                                            setShowSetupModal(true)
                                        }
                                    >
                                        {({ processing }) => (
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                Aktifkan 2FA
                                            </Button>
                                        )}
                                    </Form>
                                </div>
                            </div>
                        )}

                        <TwoFactorSetupModal
                            isOpen={showSetupModal}
                            onClose={() => setShowSetupModal(false)}
                            requiresConfirmation={requiresConfirmation}
                            twoFactorEnabled={twoFactorEnabled}
                            twoFactorConfirmed={twoFactorConfirmed}
                            qrCodeSvg={qrCodeSvg}
                            manualSetupKey={manualSetupKey}
                            clearSetupData={clearSetupData}
                            fetchSetupData={fetchSetupData}
                            errors={errors}
                        />
                    </section>
                )}

                {sessions.length > 0 && (
                    <section className="flex flex-col gap-6">
                        <Heading
                            variant="small"
                            title="Sesi browser"
                            description="Kelola dan keluarkan sesi aktif Anda di browser dan perangkat lain."
                        />

                        <div className="flex flex-col gap-4">
                            <p className="text-sm text-muted-foreground">
                                Jika perlu, Anda dapat keluar dari semua sesi
                                browser Anda yang lain di semua perangkat Anda.
                                Beberapa sesi terbaru Anda tercantum di bawah
                                ini; namun, daftar ini mungkin tidak lengkap.
                                Jika Anda merasa akun Anda telah disusupi, Anda
                                juga harus memperbarui kata sandi Anda.
                            </p>

                            <div className="flex flex-col gap-4">
                                {sessions.map((session) => (
                                    <div
                                        key={session.id}
                                        className="flex items-center gap-4 rounded-lg border p-4"
                                    >
                                        <div className="text-muted-foreground">
                                            {session.agent.is_desktop ? (
                                                <Monitor className="size-8" />
                                            ) : (
                                                <Smartphone className="size-8" />
                                            )}
                                        </div>

                                        <div className="flex flex-col">
                                            <div className="text-sm font-medium">
                                                {session.agent.platform} -{' '}
                                                {session.agent.browser}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {session.ip_address} •{' '}
                                                {session.is_current_device ? (
                                                    <span className="font-semibold text-green-600 dark:text-green-400">
                                                        Perangkat ini
                                                    </span>
                                                ) : (
                                                    <span>
                                                        Terakhir aktif{' '}
                                                        {session.last_active}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="mt-2">
                                <Dialog
                                    open={confirmingLogout}
                                    onOpenChange={setConfirmingLogout}
                                >
                                    <DialogTrigger asChild>
                                        <Button variant="outline">
                                            Keluar dari Sesi Browser Lain
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>
                                                Keluar dari Sesi Browser Lain
                                            </DialogTitle>
                                            <DialogDescription>
                                                Silakan masukkan kata sandi Anda
                                                untuk mengonfirmasi bahwa Anda
                                                ingin keluar dari sesi browser
                                                Anda yang lain di semua
                                                perangkat Anda.
                                            </DialogDescription>
                                        </DialogHeader>

                                        <Form
                                            {...SecurityController.destroy.form()}
                                            onSuccess={() =>
                                                setConfirmingLogout(false)
                                            }
                                        >
                                            {({ errors, processing }) => (
                                                <div className="space-y-4">
                                                    <div className="grid gap-2">
                                                        <Label htmlFor="password_session">
                                                            Kata sandi
                                                        </Label>
                                                        <PasswordInput
                                                            id="password_session"
                                                            name="password"
                                                            placeholder="Masukkan kata sandi Anda"
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.password
                                                            }
                                                        />
                                                    </div>

                                                    <DialogFooter>
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            onClick={() =>
                                                                setConfirmingLogout(
                                                                    false,
                                                                )
                                                            }
                                                        >
                                                            Batal
                                                        </Button>
                                                        <Button
                                                            type="submit"
                                                            disabled={
                                                                processing
                                                            }
                                                        >
                                                            Keluar dari Sesi
                                                            Browser Lain
                                                        </Button>
                                                    </DialogFooter>
                                                </div>
                                            )}
                                        </Form>
                                    </DialogContent>
                                </Dialog>
                            </div>
                        </div>
                    </section>
                )}
            </div>
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'Pengaturan keamanan',
            href: settings.security.edit(),
        },
    ],
};
