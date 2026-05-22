import { useEffect, useRef, useState } from 'react';
import { Separator } from '@/components/ui/separator';
import { BrowserSessionsSection } from '@/features/settings/components/security/BrowserSessionsSection';
import { PasswordSection } from '@/features/settings/components/security/PasswordSection';
import { TwoFactorSection } from '@/features/settings/components/security/TwoFactorSection';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import type { SecurityPageProps } from '@/features/settings/types';

export default function SecurityPage({
    canManageTwoFactor = false,
    requiresConfirmation = false,
    twoFactorEnabled = false,
    twoFactorConfirmed = false,
    sessions = [],
}: SecurityPageProps) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
    const [showSetupModal, setShowSetupModal] = useState(false);
    const prevTwoFactorEnabled = useRef(twoFactorEnabled);

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

    useEffect(() => {
        if (prevTwoFactorEnabled.current && !twoFactorEnabled) {
            clearTwoFactorAuthData();
        }

        prevTwoFactorEnabled.current = twoFactorEnabled;
    }, [twoFactorEnabled, clearTwoFactorAuthData]);

    return (
        <div className="flex flex-col gap-10">
            <PasswordSection
                currentPasswordInput={currentPasswordInput}
                passwordInput={passwordInput}
            />

            {canManageTwoFactor ? <Separator /> : null}

            <TwoFactorSection
                canManageTwoFactor={canManageTwoFactor}
                requiresConfirmation={requiresConfirmation}
                twoFactorEnabled={twoFactorEnabled}
                twoFactorConfirmed={twoFactorConfirmed}
                showSetupModal={showSetupModal}
                setShowSetupModal={setShowSetupModal}
                authState={{
                    qrCodeSvg,
                    manualSetupKey,
                    clearSetupData,
                    fetchSetupData,
                    recoveryCodesList,
                    fetchRecoveryCodes,
                    errors,
                }}
            />

            {sessions.length > 0 ? <Separator /> : null}

            <BrowserSessionsSection sessions={sessions} />
        </div>
    );
}
