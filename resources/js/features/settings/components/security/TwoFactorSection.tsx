import { Form } from '@inertiajs/react';
import { CheckCircle2, ShieldAlert, ShieldCheck } from 'lucide-react';
import { Button } from '@/components/ui/button';
import TwoFactorRecoveryCodes from '@/features/auth/components/TwoFactorRecoveryCodes';
import TwoFactorSetupModal from '@/features/auth/components/TwoFactorSetupModal';
import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';
import type { UseTwoFactorAuthReturn } from '@/hooks/use-two-factor-auth';
import { cn } from '@/lib/utils';
import { disable, enable } from '@/routes/two-factor';

interface TwoFactorSectionProps {
    canManageTwoFactor: boolean;
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
    twoFactorConfirmed: boolean;
    showSetupModal: boolean;
    setShowSetupModal: (value: boolean) => void;
    authState: Pick<
        UseTwoFactorAuthReturn,
        | 'qrCodeSvg'
        | 'manualSetupKey'
        | 'clearSetupData'
        | 'fetchSetupData'
        | 'recoveryCodesList'
        | 'fetchRecoveryCodes'
        | 'errors'
    >;
}

export function TwoFactorSection({
    canManageTwoFactor,
    requiresConfirmation,
    twoFactorEnabled,
    twoFactorConfirmed,
    showSetupModal,
    setShowSetupModal,
    authState,
}: TwoFactorSectionProps) {
    if (!canManageTwoFactor) {
        return null;
    }

    const isConfirmed = !requiresConfirmation || twoFactorConfirmed;

    return (
        <section className="flex flex-col gap-6">
            <SettingsSectionHeader
                title="Otentikasi dua faktor"
                description="Tingkatkan keamanan akun Anda dengan menggunakan otentikasi dua faktor."
                icon={twoFactorEnabled && isConfirmed ? ShieldCheck : ShieldAlert}
                iconClassName={cn(
                    twoFactorEnabled && isConfirmed
                        ? 'bg-green-100 text-green-600 dark:bg-green-950/50 dark:text-green-400'
                        : undefined,
                )}
            />

            <div className="flex flex-col items-start gap-4">
                {twoFactorEnabled ? (
                    isConfirmed ? (
                        <>
                            <div className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950/60 dark:text-green-400">
                                <CheckCircle2 className="h-3 w-3" />
                                Aktif
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Anda telah mengaktifkan otentikasi dua faktor.
                                Anda akan dimintai PIN aman saat login, yang
                                dapat Anda ambil dari aplikasi TOTP di ponsel
                                Anda.
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
                                recoveryCodesList={authState.recoveryCodesList}
                                fetchRecoveryCodes={
                                    authState.fetchRecoveryCodes
                                }
                                errors={authState.errors}
                            />
                        </>
                    ) : (
                        <>
                            <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-800/40 dark:bg-amber-950/30 dark:text-amber-400">
                                Anda belum selesai mengonfigurasi otentikasi
                                dua faktor. Selesaikan pengaturan untuk
                                meningkatkan keamanan akun Anda.
                            </div>
                            <div className="flex items-center gap-3">
                                <Button
                                    type="button"
                                    onClick={() => setShowSetupModal(true)}
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
                    )
                ) : (
                    <>
                        <p className="text-sm text-muted-foreground">
                            Saat Anda mengaktifkan otentikasi dua faktor, Anda
                            akan dimintai PIN aman saat login. PIN ini dapat
                            diambil dari aplikasi TOTP di ponsel Anda.
                        </p>
                        <Form
                            {...enable.form()}
                            onSuccess={() => setShowSetupModal(true)}
                        >
                            {({ processing }) => (
                                <Button type="submit" disabled={processing}>
                                    Aktifkan 2FA
                                </Button>
                            )}
                        </Form>
                    </>
                )}
            </div>

            <TwoFactorSetupModal
                isOpen={showSetupModal}
                onClose={() => setShowSetupModal(false)}
                requiresConfirmation={requiresConfirmation}
                twoFactorEnabled={twoFactorEnabled}
                twoFactorConfirmed={twoFactorConfirmed}
                qrCodeSvg={authState.qrCodeSvg}
                manualSetupKey={authState.manualSetupKey}
                clearSetupData={authState.clearSetupData}
                fetchSetupData={authState.fetchSetupData}
                errors={authState.errors}
            />
        </section>
    );
}
