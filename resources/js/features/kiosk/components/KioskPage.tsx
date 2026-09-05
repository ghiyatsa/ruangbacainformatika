import { Head } from '@inertiajs/react';
import { PinStep } from '@/features/kiosk/components/PinStep';
import { ReadyStep } from '@/features/kiosk/components/ReadyStep';
import type { KioskProps } from '@/features/kiosk/types';

export default function KioskPage(props: KioskProps) {
    return (
        <div className="min-h-dvh bg-background font-sans text-foreground selection:bg-primary/10 selection:text-primary">
            <Head
                title={
                    props.step === 'pin'
                        ? 'Buka Kiosk'
                        : 'Layanan Mandiri Ruang Baca'
                }
            >
                <meta
                    head-key="robots"
                    name="robots"
                    content="noindex, nofollow"
                />
            </Head>

            <main className="mx-auto flex min-h-dvh w-full max-w-7xl items-center px-4 py-4 sm:px-6 lg:px-8">
                {props.step === 'pin' ? <PinStep /> : <ReadyStep {...props} />}
            </main>
        </div>
    );
}
