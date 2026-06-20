import { useEffect, useState } from 'react';
import { formatCountdown } from '@/lib/format-countdown';

export interface CountdownState {
    /** Seconds remaining until expiry, or `null` when no expiry is set. */
    remainingSeconds: number | null;
    /** Pre-formatted countdown label, or `null` when no expiry is set. */
    countdownLabel: string | null;
}

/**
 * Track the time remaining until an ISO expiry timestamp, re-rendering once
 * per second. Pass `null` (or an empty string) to disable the countdown — the
 * hook then reports `null` for both fields and runs no interval.
 */
export function useCountdown(expiresAtIso: string | null): CountdownState {
    const [currentTimestamp, setCurrentTimestamp] = useState(() => Date.now());

    const expiresAtTimestamp = expiresAtIso
        ? new Date(expiresAtIso).getTime()
        : null;

    const remainingSeconds =
        expiresAtTimestamp === null
            ? null
            : Math.max(
                  Math.ceil((expiresAtTimestamp - currentTimestamp) / 1000),
                  0,
              );

    useEffect(() => {
        if (expiresAtTimestamp === null) {
            return;
        }

        const interval = window.setInterval(() => {
            setCurrentTimestamp(Date.now());
        }, 1000);

        return () => window.clearInterval(interval);
    }, [expiresAtTimestamp]);

    return {
        remainingSeconds,
        countdownLabel:
            remainingSeconds === null
                ? null
                : formatCountdown(remainingSeconds),
    };
}
