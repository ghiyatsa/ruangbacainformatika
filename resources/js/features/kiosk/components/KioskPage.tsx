import { Head, router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { PinStep } from '@/features/kiosk/components/PinStep';
import { ReadyStep } from '@/features/kiosk/components/ReadyStep';
import { getCsrfToken } from '@/lib/csrf';
import type { KioskProps, KioskSessionConfig } from '@/features/kiosk/types';

function shouldLockForOperatingHours(config: KioskSessionConfig): boolean {
    if (config.persistentForDevelopment) {
        return false;
    }

    if (!config.withinOperatingHours || !config.sessionExpiresAtIso) {
        return true;
    }

    return Date.now() >= new Date(config.sessionExpiresAtIso).getTime();
}

export default function KioskPage(props: KioskProps) {
    const isLockingRef = useRef(false);

    useEffect(() => {
        if (props.step !== 'ready') {
            return;
        }

        const lockKiosk = async () => {
            if (isLockingRef.current) {
                return;
            }

            isLockingRef.current = true;

            try {
                const csrfToken = getCsrfToken();

                await fetch(KioskController.lock.url(), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                });
            } finally {
                router.visit(KioskController.show(), {
                    replace: true,
                    preserveScroll: true,
                    preserveState: false,
                });
            }
        };

        const interval = window.setInterval(() => {
            if (shouldLockForOperatingHours(props.kioskSession)) {
                void lockKiosk();
            }
        }, 15000);

        if (shouldLockForOperatingHours(props.kioskSession)) {
            void lockKiosk();
        }

        return () => {
            window.clearInterval(interval);
        };
    }, [props.kioskSession, props.step]);

    return (
        <div className="min-h-dvh bg-background font-sans text-foreground selection:bg-primary/10 selection:text-primary">
            <Head
                title={
                    props.step === 'pin'
                        ? 'PIN Kios'
                        : 'Layanan Mandiri Perpustakaan'
                }
            />

            <main className="container mx-auto flex min-h-dvh max-w-7xl items-center px-4 py-4 sm:px-6 lg:px-8">
                {props.step === 'pin' ? (
                    <PinStep />
                ) : (
                    <ReadyStep {...props} />
                )}
            </main>
        </div>
    );
}
