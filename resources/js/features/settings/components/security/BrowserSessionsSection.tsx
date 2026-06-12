import { Link } from '@inertiajs/react';
import { Monitor, Smartphone } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';
import { logout } from '@/routes';
import type { Session } from '@/features/settings/types';

interface BrowserSessionsSectionProps {
    sessions: Session[];
}

export function BrowserSessionsSection({
    sessions,
}: BrowserSessionsSectionProps) {
    const otherSessions = sessions.filter(
        (session) => !session.is_current_device,
    );

    return (
        <section className="rounded-xl border border-border/70 bg-card p-6 shadow-xs">
            <div className="flex flex-col gap-4">
                <SettingsSectionHeader title="Sesi browser" />

                {otherSessions.length > 0 ? (
                    <div className="flex flex-col gap-2">
                        {otherSessions.map((session) => (
                            <div
                                key={session.id}
                                className="flex items-center gap-4 rounded-xl border border-border/70 bg-muted/20 p-4"
                            >
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-muted-foreground shadow-xs">
                                    {session.agent.is_desktop ? (
                                        <Monitor className="h-5 w-5" />
                                    ) : (
                                        <Smartphone className="h-5 w-5" />
                                    )}
                                </div>

                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-medium">
                                        {session.agent.platform} -{' '}
                                        {session.agent.browser}
                                    </p>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        {session.ip_address} -{' '}
                                        {session.last_active}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="py-8 text-sm">
                        Tidak ada sesi lain yang aktif.
                    </div>
                )}

                <div>
                    <Button asChild variant="outline">
                        <Link href={logout().url} method="post" as="button">
                            Keluar dari perangkat ini
                        </Link>
                    </Button>
                </div>
            </div>
        </section>
    );
}
