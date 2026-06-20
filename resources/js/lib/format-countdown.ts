/**
 * Format a remaining duration (in seconds) as a compact countdown string.
 *
 * - Durations of an hour or longer render as `HH:MM:SS`.
 * - Shorter durations render as `MM:SS`.
 * - Zero and negative inputs collapse to `00:00`.
 */
export function formatCountdown(totalSeconds: number): string {
    if (totalSeconds <= 0) {
        return '00:00';
    }

    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    if (hours > 0) {
        return [hours, minutes, seconds]
            .map((value) => String(value).padStart(2, '0'))
            .join(':');
    }

    return [minutes, seconds]
        .map((value) => String(value).padStart(2, '0'))
        .join(':');
}
