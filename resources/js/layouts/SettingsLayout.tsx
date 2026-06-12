import { Link, usePage } from '@inertiajs/react';
import { KeyRound, ShieldCheck, User } from 'lucide-react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import settings from '@/routes/settings';
import type { PropsWithChildren } from 'react';
import type { Auth, NavItem } from '@/types';

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const sidebarNavItems: NavItem[] = [
        {
            title: 'Profil',
            href: settings.profile.edit(),
            icon: User,
        },
        {
            title: 'Keamanan',
            href: settings.security.edit(),
            icon: ShieldCheck,
        },
        ...(auth.canBorrowBooks
            ? [
                  {
                      title: 'Member Key',
                      href: settings.memberKey.show(),
                      icon: KeyRound,
                  },
              ]
            : []),
    ];

    return (
        <div className="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-6xl space-y-6">
                <h1 className="text-2xl font-semibold tracking-tight">
                    Pengaturan
                </h1>

                <div className="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
                    <aside className="xl:sticky xl:top-24 xl:self-start">
                        <nav
                            className="grid gap-2"
                            aria-label="Navigasi pengaturan"
                        >
                            {sidebarNavItems.map((item, index) => {
                                const isActive = isCurrentOrParentUrl(item.href);

                                return (
                                    <Link
                                        key={`${toUrl(item.href)}-${index}`}
                                        href={item.href}
                                        className={cn(
                                            'group flex items-center gap-3 rounded-xl border px-4 py-3 text-sm transition-colors',
                                            isActive
                                                ? 'border-primary/30 bg-primary/5 text-foreground'
                                                : 'border-border/70 bg-card text-muted-foreground hover:bg-accent/60 hover:text-foreground',
                                        )}
                                    >
                                        {item.icon ? (
                                            <div
                                                className={cn(
                                                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-muted text-muted-foreground transition-colors',
                                                    isActive
                                                        ? 'bg-primary text-primary-foreground'
                                                        : 'group-hover:bg-background group-hover:text-foreground',
                                                )}
                                            >
                                                <item.icon className="h-4 w-4" />
                                            </div>
                                        ) : null}
                                        <p className="font-medium">
                                            {item.title}
                                        </p>
                                    </Link>
                                );
                            })}
                        </nav>
                    </aside>

                    <div className="min-w-0 space-y-6">{children}</div>
                </div>
            </div>
        </div>
    );
}
