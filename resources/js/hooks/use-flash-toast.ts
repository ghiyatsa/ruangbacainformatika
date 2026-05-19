import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import type { FlashToast } from '@/types/ui';

const RECENT_TOAST_WINDOW_MS = 750;

let lastToastState: {
    signature: string;
    displayedAt: number;
} | null = null;

export function useFlashToast(): void {
    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            const data = flash?.toast as FlashToast | undefined;

            if (!data) {
                return;
            }

            const signature = `${data.type}:${data.message}`;
            const now = Date.now();

            if (
                lastToastState?.signature === signature &&
                now - lastToastState.displayedAt < RECENT_TOAST_WINDOW_MS
            ) {
                return;
            }

            lastToastState = {
                signature,
                displayedAt: now,
            };

            toast[data.type](data.message, {
                id: signature,
            });
        });
    }, []);
}
