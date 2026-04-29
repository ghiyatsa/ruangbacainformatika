import { Head } from '@inertiajs/react';
import { PinStep } from '@/components/kiosk/PinStep';
import { ReadyStep } from '@/components/kiosk/ReadyStep';
import type { KioskProps } from './types';

export default function KioskIndex(props: KioskProps) {
    return (
        <div className="min-h-screen bg-background font-sans text-foreground">
            <Head title={`${props.pageTitle} - ${props.siteName}`} />

            <main className="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
                {props.step === 'pin' ? (
                    <PinStep {...props} />
                ) : (
                    <ReadyStep {...props} />
                )}
            </main>
        </div>
    );
}
