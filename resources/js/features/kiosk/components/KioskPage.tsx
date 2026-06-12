import { Head, router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import * as KioskController from '@/actions/App/Http/Controllers/KioskController';
import { PinStep } from '@/features/kiosk/components/PinStep';
import { ReadyStep } from '@/features/kiosk/components/ReadyStep';
import type { KioskProps, KioskSessionConfig } from '@/features/kiosk/types';

function getCsrfToken(): string | null {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? null
    );
}

function getMinutesInTimezone(date: Date, timezone: string): number {
    const formatter = new Intl.DateTimeFormat('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone: timezone,
    });
    const parts = formatter.formatToParts(date);
    const hours = Number(
        parts.find((part) => part.type === 'hour')?.value ?? 0,
    );
    const minutes = Number(
        parts.find((part) => part.type === 'minute')?.value ?? 0,
    );

    return hours * 60 + minutes;
}

function getMinutesFromTime(time: string): number {
    const [hours = '0', minutes = '0'] = time.split(':');

    return Number(hours) * 60 + Number(minutes);
}

function isWithinOperatingHours(
    config: KioskSessionConfig,
    date: Date,
): boolean {
    const openMinutes = getMinutesFromTime(config.operatingOpenTime);
    const closeMinutes = getMinutesFromTime(config.operatingCloseTime);
    const currentMinutes = getMinutesInTimezone(date, config.timezone);

    if (openMinutes === closeMinutes) {
        return true;
    }

    if (openMinutes < closeMinutes) {
        return currentMinutes >= openMinutes && currentMinutes < closeMinutes;
    }

    return currentMinutes >= openMinutes || currentMinutes < closeMinutes;
}

function getIdleTimeoutMs(config: KioskSessionConfig, date: Date): number {
    const idleMinutes = isWithinOperatingHours(config, date)
        ? config.idleTimeoutOpenMinutes
        : config.idleTimeoutClosedMinutes;

    return Math.max(idleMinutes, 1) * 60 * 1000;
}

export default function KioskPage(props: KioskProps) {
    const lastInteractionAtRef = useRef(0);
    const isLockingRef = useRef(false);

    useEffect(() => {
        if (props.step !== 'ready') {
            return;
        }

        lastInteractionAtRef.current = Date.now();

        const markInteraction = () => {
            lastInteractionAtRef.current = Date.now();
        };

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
            const now = new Date();
            const idleTimeoutMs = getIdleTimeoutMs(props.kioskSession, now);
            const idleDurationMs = Date.now() - lastInteractionAtRef.current;

            if (idleDurationMs >= idleTimeoutMs) {
                void lockKiosk();
            }
        }, 15000);

        const eventNames: Array<keyof WindowEventMap> = [
            'click',
            'keydown',
            'mousemove',
            'scroll',
            'touchstart',
        ];

        for (const eventName of eventNames) {
            window.addEventListener(eventName, markInteraction, {
                passive: true,
            });
        }

        return () => {
            window.clearInterval(interval);

            for (const eventName of eventNames) {
                window.removeEventListener(eventName, markInteraction);
            }
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
