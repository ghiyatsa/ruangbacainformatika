import { useEffect } from 'react';

const IDLE_EVENTS: (keyof WindowEventMap)[] = [
    'pointerdown',
    'keydown',
    'wheel',
    'touchstart',
];

export interface UseIdleResetOptions {
    timeoutMs?: number;
    enabled: boolean;
    onIdle: () => void;
}

export function useIdleReset({
    timeoutMs = 90_000,
    enabled,
    onIdle,
}: UseIdleResetOptions): void {
    useEffect(() => {
        if (!enabled) {
            return;
        }

        let timer = window.setTimeout(onIdle, timeoutMs);

        const resetTimer = () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(onIdle, timeoutMs);
        };

        for (const event of IDLE_EVENTS) {
            window.addEventListener(event, resetTimer, { passive: true });
        }

        return () => {
            window.clearTimeout(timer);

            for (const event of IDLE_EVENTS) {
                window.removeEventListener(event, resetTimer);
            }
        };
    }, [enabled, onIdle, timeoutMs]);
}
