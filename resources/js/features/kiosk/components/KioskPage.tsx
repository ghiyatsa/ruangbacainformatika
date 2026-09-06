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

            <main className="mx-auto flex min-h-dvh w-full items-center px-2 py-2 sm:px-3 sm:py-3 lg:px-4 lg:py-4">
                {props.step === 'pin' ? <PinStep /> : <ReadyStep {...props} />}
            </main>
        </div>
    );
}
