import { Form } from '@inertiajs/react';
import {
    CheckCircle2,
    Key,
    Monitor,
    ShieldAlert,
    ShieldCheck,
    Smartphone,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
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
import { Separator } from '@/components/ui/separator';
import TwoFactorRecoveryCodes from '@/features/auth/components/TwoFactorRecoveryCodes';
import TwoFactorSetupModal from '@/features/auth/components/TwoFactorSetupModal';
import type { SecurityPageProps } from '@/features/settings/types';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import { cn } from '@/lib/utils';
import { disable, enable } from '@/routes/two-factor';

export default function SecurityPage({
    canManageTwoFactor = false,
    requiresConfirmation = false,
    twoFactorEnabled = false,
    twoFactorConfirmed = false,
    sessions = [],
}: SecurityPageProps) {
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

    const [showSetupModal, setShowSetupModal] = useState(false);
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
        <div className="flex flex-col gap-10">
            {/* ── Change Password ── */}
            <section className="flex flex-col gap-6">
                <div className="flex items-start gap-3">
                    <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <Key className="h-4 w-4" />
                    </div>
                    <div>
                        <h2 className="text-base font-semibold">
                            Perbarui kata sandi
                        </h2>
                        <p className="mt-0.5 text-sm text-muted-foreground">
                            Pastikan akun Anda menggunakan kata sandi yang
                            panjang dan acak agar tetap aman.
                        </p>
                    </div>
                </div>

                <Form
                    {...SecurityController.update.form()}
                    options={{ preserveScroll: true }}
                    resetOnError={[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]}
                    resetOnSuccess
                    onError={(formErrors) => {
                        if (formErrors.password) {
                            passwordInput.current?.focus();
                        }

                        if (formErrors.current_password) {
                            currentPasswordInput.current?.focus();
                        }
                    }}
                    className="flex flex-col gap-5"
                >
                    {({
                        errors: formErrors,
                        processing,
                        recentlySuccessful,
                    }) => (
                        <div className="flex flex-col gap-5">
                            <div className="grid gap-2">
                                <Label htmlFor="current_password">
                                    Kata sandi saat ini
                                </Label>
                                <PasswordInput
                                    id="current_password"
                                    ref={currentPasswordInput}
                                    name="current_password"
                                    className="w-full"
                                    autoComplete="current-password"
                                    placeholder="Kata sandi saat ini"
                                />
                                <InputError
                                    message={formErrors.current_password}
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
                                    className="w-full"
                                    autoComplete="new-password"
                                    placeholder="Kata sandi baru"
                                />
                                <InputError message={formErrors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Konfirmasi kata sandi baru
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    className="w-full"
                                    autoComplete="new-password"
                                    placeholder="Konfirmasi kata sandi baru"
                                />
                                <InputError
                                    message={formErrors.password_confirmation}
                                />
                            </div>

                            <div className="flex items-center gap-3 pt-1">
                                <Button
                                    disabled={processing}
                                    data-test="update-password-button"
                                >
                                    {processing
                                        ? 'Menyimpan…'
                                        : 'Simpan kata sandi'}
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

            {/* ── Two Factor Auth ── */}
            {canManageTwoFactor && (
                <>
                    <Separator />
                    <section className="flex flex-col gap-6">
                        <div className="flex items-start gap-3">
                            <div
                                className={cn(
                                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl',
                                    twoFactorEnabled && isConfirmed
                                        ? 'bg-green-100 text-green-600 dark:bg-green-950/50 dark:text-green-400'
                                        : 'bg-primary/10 text-primary',
                                )}
                            >
                                {twoFactorEnabled && isConfirmed ? (
                                    <ShieldCheck className="h-4 w-4" />
                                ) : (
                                    <ShieldAlert className="h-4 w-4" />
                                )}
                            </div>
                            <div className="flex-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <h2 className="text-base font-semibold">
                                        Otentikasi dua faktor
                                    </h2>
                                    {twoFactorEnabled && isConfirmed && (
                                        <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950/60 dark:text-green-400">
                                            <CheckCircle2 className="h-3 w-3" />
                                            Aktif
                                        </span>
                                    )}
                                </div>
                                <p className="mt-0.5 text-sm text-muted-foreground">
                                    Tingkatkan keamanan akun Anda dengan
                                    menggunakan otentikasi dua faktor.
                                </p>
                            </div>
                        </div>

                        {twoFactorEnabled ? (
                            <div className="flex flex-col items-start gap-4">
                                {isConfirmed ? (
                                    <>
                                        <p className="text-sm text-muted-foreground">
                                            Anda telah mengaktifkan otentikasi
                                            dua faktor. Anda akan dimintai pin
                                            acak yang aman saat login, yang
                                            dapat Anda ambil dari aplikasi yang
                                            didukung TOTP di ponsel Anda.
                                        </p>
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
                                        <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-800/40 dark:bg-amber-950/30 dark:text-amber-400">
                                            Anda belum selesai mengonfigurasi
                                            otentikasi dua faktor. Selesaikan
                                            pengaturan untuk meningkatkan
                                            keamanan akun Anda.
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <Button
                                                onClick={() =>
                                                    setShowSetupModal(true)
                                                }
                                            >
                                                <ShieldCheck className="h-4 w-4" />
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
                            <div className="flex flex-col items-start gap-4">
                                <p className="text-sm text-muted-foreground">
                                    Saat Anda mengaktifkan otentikasi dua
                                    faktor, Anda akan dimintai pin yang aman
                                    saat login. Pin ini dapat diambil dari
                                    aplikasi yang didukung TOTP di ponsel Anda.
                                </p>
                                <Form
                                    {...enable.form()}
                                    onSuccess={() => setShowSetupModal(true)}
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
                </>
            )}

            {/* ── Browser Sessions ── */}
            {sessions.length > 0 && (
                <>
                    <Separator />
                    <section className="flex flex-col gap-6">
                        <div>
                            <h2 className="text-base font-semibold">
                                Sesi browser
                            </h2>
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                Kelola dan keluarkan sesi aktif Anda di browser
                                dan perangkat lain.
                            </p>
                        </div>

                        <div className="flex flex-col gap-4">
                            <p className="text-sm text-muted-foreground">
                                Jika perlu, Anda dapat keluar dari semua sesi
                                browser Anda yang lain di semua perangkat Anda.
                                Jika Anda merasa akun Anda telah disusupi, Anda
                                juga harus memperbarui kata sandi Anda.
                            </p>

                            <div className="flex flex-col gap-2">
                                {sessions.map((session) => (
                                    <div
                                        key={session.id}
                                        className={cn(
                                            'flex items-center gap-4 rounded-xl border p-4 transition-colors',
                                            session.is_current_device
                                                ? 'border-primary/30 bg-primary/5'
                                                : 'bg-muted/40',
                                        )}
                                    >
                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-muted-foreground shadow-xs">
                                            {session.agent.is_desktop ? (
                                                <Monitor className="h-5 w-5" />
                                            ) : (
                                                <Smartphone className="h-5 w-5" />
                                            )}
                                        </div>

                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span className="text-sm font-medium">
                                                    {session.agent.platform} —{' '}
                                                    {session.agent.browser}
                                                </span>
                                                {session.is_current_device && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950/60 dark:text-green-400">
                                                        <CheckCircle2 className="h-3 w-3" />
                                                        Perangkat ini
                                                    </span>
                                                )}
                                            </div>
                                            <p className="mt-0.5 text-xs text-muted-foreground">
                                                {session.ip_address}
                                                {!session.is_current_device && (
                                                    <>
                                                        {' '}
                                                        • {session.last_active}
                                                    </>
                                                )}
                                            </p>
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
                                            {({
                                                errors: formErrors,
                                                processing,
                                            }) => (
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
                                                                formErrors.password
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
                </>
            )}
        </div>
    );
}
