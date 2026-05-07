import { Head } from '@inertiajs/react';
import { PinStep } from '@/features/kiosk/components/PinStep';
import { ReadyStep } from '@/features/kiosk/components/ReadyStep';
import type { KioskProps } from '@/features/kiosk/types';

export default function KioskPage(props: KioskProps) {
    return (
        <div className="relative min-h-screen bg-background font-sans text-foreground selection:bg-primary/10 selection:text-primary">
            <Head title={props.pageTitle} />

            {/* Dot-grid texture – matches catalog & welcome pages */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.05]"
                style={{
                    backgroundImage:
                        'radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)',
                    backgroundSize: '24px 24px',
                }}
            />

            {/* Ambient gradient blobs */}
            <div className="pointer-events-none fixed inset-0 z-0 overflow-hidden">
                <div className="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-primary/5 blur-3xl" />
                <div className="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-primary/5 blur-3xl" />
            </div>

            <main className="relative z-10 flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
                {props.step === 'pin' ? (
                    <PinStep {...props} />
                ) : (
                    <ReadyStep {...props} />
                )}
            </main>
        </div>
    );
}
