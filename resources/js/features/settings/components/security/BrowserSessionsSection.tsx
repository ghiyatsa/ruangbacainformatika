import { Link } from '@inertiajs/react';
import { CheckCircle2, Monitor, Smartphone } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { SettingsSectionHeader } from '@/features/settings/components/shared/SettingsSectionHeader';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import type { Session } from '@/features/settings/types';

interface BrowserSessionsSectionProps {
    sessions: Session[];
}

export function BrowserSessionsSection({
    sessions,
}: BrowserSessionsSectionProps) {
    if (sessions.length === 0) {
        return null;
    }

    return (
        <section className="flex flex-col gap-6">
            <SettingsSectionHeader
                title="Sesi browser"
                description="Lihat sesi aktif akun Anda di browser dan perangkat lain."
            />

            <div className="flex flex-col gap-4">
                <p className="text-sm text-muted-foreground">
                    Daftar ini membantu Anda meninjau perangkat yang masih
                    terhubung ke akun Anda.
                </p>

                <div className="flex flex-col gap-2">
                    {sessions.map((session) => (
                        <div
                            key={session.id}
                            className={cn(
                                'flex items-center gap-4 rounded-xl border p-4 transition-colors',
                                session.is_current_device
                                    ? 'border-primary/30 bg-primary/5'
                                    : 'bg-muted/40',
                            )}
                        >
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-muted-foreground shadow-xs">
                                {session.agent.is_desktop ? (
                                    <Monitor className="h-5 w-5" />
                                ) : (
                                    <Smartphone className="h-5 w-5" />
                                )}
                            </div>

                            <div className="min-w-0 flex-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="text-sm font-medium">
                                        {session.agent.platform} -{' '}
                                        {session.agent.browser}
                                    </span>
                                    {session.is_current_device ? (
                                        <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950/60 dark:text-green-400">
                                            <CheckCircle2 className="h-3 w-3" />
                                            Perangkat ini
                                        </span>
                                    ) : null}
                                </div>
                                <p className="mt-0.5 text-xs text-muted-foreground">
                                    {session.ip_address}
                                    {!session.is_current_device ? (
                                        <> - {session.last_active}</>
                                    ) : null}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>

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
