import { Link, usePage } from '@inertiajs/react';
import { BadgeCheck, QrCode, ShieldCheck, User, UserRoundCog } from 'lucide-react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import settings from '@/routes/settings';
import type { PropsWithChildren } from 'react';
import type { Auth, NavItem } from '@/types';

function formatJoinedDate(value?: string): string | null {
    if (!value) {
        return null;
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const user = auth.user;
    const joinedDate = formatJoinedDate(user?.created_at);
    const initials = (user?.name ?? '')
        .split(' ')
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase() ?? '')
        .join('');
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
                      title: 'QR Anggota',
                      href: settings.memberQr.show(),
                      icon: QrCode,
                  },
              ]
            : []),
    ];

    return (
        <div className="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-6xl space-y-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm font-medium text-muted-foreground">
                            Akun pengguna
                        </p>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Pengaturan
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Kelola profil, keamanan, dan akses layanan akun Anda.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Badge
                            variant={
                                auth.hasVerifiedWhatsApp ? 'success' : 'outline'
                            }
                        >
                            {auth.hasVerifiedWhatsApp ? (
                                <>
                                    <BadgeCheck className="h-3.5 w-3.5" />
                                    WhatsApp terverifikasi
                                </>
                            ) : (
                                'WhatsApp belum verifikasi'
                            )}
                        </Badge>
                        <Badge
                            variant={auth.canBorrowBooks ? 'success' : 'outline'}
                        >
                            {auth.canBorrowBooks
                                ? 'Layanan pinjam aktif'
                                : 'Layanan pinjam belum aktif'}
                        </Badge>
                        {auth.canAccessAdminPanel ? (
                            <Badge variant="secondary">Akses admin</Badge>
                        ) : null}
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
                    <aside className="space-y-4 xl:sticky xl:top-24 xl:self-start">
                        <div className="rounded-xl border border-border/70 bg-card p-5 shadow-xs">
                            <div className="flex items-start gap-3">
                                <Avatar className="h-14 w-14 rounded-2xl">
                                    <AvatarImage
                                        src={user?.avatar ?? undefined}
                                        alt={user?.name ?? 'User avatar'}
                                        className="object-cover"
                                    />
                                    <AvatarFallback className="rounded-2xl bg-primary text-base font-semibold text-primary-foreground">
                                        {initials || (
                                            <UserRoundCog className="h-5 w-5" />
                                        )}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-base font-semibold">
                                        {user?.name}
                                    </p>
                                    <p className="truncate text-sm text-muted-foreground">
                                        {user?.email}
                                    </p>
                                    {joinedDate ? (
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            Akun sejak {joinedDate}
                                        </p>
                                    ) : null}
                                </div>
                            </div>

                            {auth.borrowingAccessMessage ? (
                                <div className="mt-4 rounded-lg bg-muted/50 p-3 text-sm text-muted-foreground">
                                    {auth.borrowingAccessMessage}
                                </div>
                            ) : null}
                        </div>

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
