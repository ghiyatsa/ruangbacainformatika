import { BrowserSessionsSection } from '@/features/settings/components/security/BrowserSessionsSection';
import type { SecurityPageProps } from '@/features/settings/types';

export default function SecurityPage({ sessions = [] }: SecurityPageProps) {
    const currentDeviceCount = sessions.filter(
        (session) => session.is_current_device,
    ).length;
    const otherDeviceCount = Math.max(sessions.length - currentDeviceCount, 0);

    return (
        <div className="space-y-6">
            <section className="rounded-xl border border-border/70 bg-card p-6 shadow-xs">
                <div className="mb-4">
                    <h2 className="text-base font-semibold">
                        Ringkasan keamanan
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Pantau perangkat yang masih terhubung ke akun Anda.
                    </p>
                </div>

                <div className="grid gap-3 sm:grid-cols-3">
                    <div className="rounded-lg border border-border/70 bg-muted/20 p-4">
                        <p className="text-sm text-muted-foreground">
                            Total sesi
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {sessions.length}
                        </p>
                    </div>
                    <div className="rounded-lg border border-border/70 bg-muted/20 p-4">
                        <p className="text-sm text-muted-foreground">
                            Perangkat ini
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {currentDeviceCount}
                        </p>
                    </div>
                    <div className="rounded-lg border border-border/70 bg-muted/20 p-4">
                        <p className="text-sm text-muted-foreground">
                            Perangkat lain
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {otherDeviceCount}
                        </p>
                    </div>
                </div>
            </section>

            <BrowserSessionsSection sessions={sessions} />
        </div>
    );
}
